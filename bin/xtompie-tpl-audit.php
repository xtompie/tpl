<?php

/**
 * Usage: php xtompie-tpl-audit.php [-p path] [-s suffix]
 * -p: Path to search (optional, default: "src")
 * -s: File suffix to search (optional, default: ".tpl.php")
 *
 * Example usage:
 *   php xtompie-tpl-audit.php -p /some/path -s .php
 *   php xtompie-tpl-audit.php -p /another/path
 *   php xtompie-tpl-audit.php -s .tpl.php
 *   php xtompie-tpl-audit.php
 */

function colorize(string $text, string $color): string
{
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'purple' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'reset' => "\033[0m"
    ];

    if (!isset($colors[$color])) {
        return $text;
    }

    return $colors[$color] . $text . $colors['reset'];
}

function main(): void
{
    $path = 'src';
    $suffix = '.tpl.php';

    $options = getopt('p:s:');

    if (isset($options['p'])) {
        $path = $options['p'];
    }

    if (isset($options['s'])) {
        $suffix = $options['s'];
    }

    if (!is_dir($path)) {
        fprintf(STDERR, "Error: Path '%s' does not exist or is not a directory.\n", $path);
        exit(1);
    }

    $allMatches = [];
    foreach (findFilesWithSuffix($path, $suffix) as $file) {
        foreach (findUnescapedEchoTags($file) as $match) {
            $allMatches[] = $match;
        }
    }

    foreach ($allMatches as $match) {
        printf("%s:%s:%s: %s\n",
            colorize($match['file'], 'red'),
            colorize($match['line'], 'red'),
            colorize($match['column'], 'red'),
            colorize($match['content'], 'red')
        );
    }

    if (count($allMatches) > 0) {
        echo "\n" . colorize("Values must be escaped with \$this->e(\$value) or use \$this->raw(\$value) for raw output.", 'yellow') . "\n";
        exit(1);
    } else {
        exit(0);
    }
}

/**
 * @return Generator<string> File paths matching the suffix
 */
function findFilesWithSuffix(string $directory, string $suffix): Generator
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && str_ends_with($file->getPathname(), $suffix)) {
            yield $file->getPathname();
        }
    }
}

/**
 * @return Generator<array{file: string, line: int, column: int, content: string}> Match data for unescaped echo tags
 */
function findUnescapedEchoTags(string $filePath): Generator
{
    $content = file_get_contents($filePath);
    if ($content === false) {
        return;
    }

    $lines = explode("\n", $content);
    $regexpToMatch = regexpToMatchUnescapedEchoTags();

    foreach ($lines as $lineNumber => $line) {
        if (preg_match_all($regexpToMatch, $line, $lineMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($lineMatches[0] as $match) {
                yield [
                    'file' => $filePath,
                    'line' => $lineNumber + 1,
                    'column' => $match[1] + 1,
                    'content' => trim($line)
                ];
            }
        }
    }
}

function regexpToMatchUnescapedEchoTags(): string
{
    return '/<\?=\s*(?!\s*\$this->)/';
}

main();
