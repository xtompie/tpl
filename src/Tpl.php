<?php

declare(strict_types=1);

namespace Xtompie\Tpl;

use Throwable;

class Tpl
{
    protected $__stack = [];
    protected $__content = '';

    public function __invoke(string $template, array $data = []): string
    {
        $this->push($template, $data);
        while ($this->__stack) {
            $level = array_pop($this->__stack);
            $this->__content = $this->render($level['template'], $level['data']);
        }

        return $this->__content;
    }

    protected function push(string $template, array $data = []): void
    {
        $this->__stack[] = ['template' => $template, 'data' => $data];
    }

    protected function content(): string
    {
        return $this->__content;
    }

    protected function templatePathPrefix(): string
    {
        return '';
    }

    protected function raw(string $string): string
    {
        return $string;
    }

    protected function e(string $string): string
    {
        return htmlspecialchars($string);
    }

    protected function render(string $template, array $data = []): string
    {
        $path = $this->templatePathPrefix() . $template;
        $level = ob_get_level();
        ob_start();
        try {
            (function() {
                extract(func_get_arg(1));
                include func_get_arg(0);
            })($path, $data);
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
        return ob_get_clean();
    }
}