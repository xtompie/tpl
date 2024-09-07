# Tpl

Tpl native PHP template system.

Simple, lightweight, no dependencies, low abstraction, low features, easy to extend.

```php
<?php

use Xtompie\Tpl\Tpl;

$tpl = new Tpl();
echo $tpl->__invoke('page.tpl.php', ['title' => 'Foobar']);
```

`page.tpl.php`:

```phtml
<?php /** @var Xtompie\Tpl\Tpl $this */ ?>

<h1><?= $this->e($title) ?></h1>
```

- uses native PHP
- not auto escape, but `vendor/bin/xtompie-tpl-audit.sh -p src -s .tpl.php` can be used
- no sections
- no blocks
- no namespaces
- autocompletion using `<?php /** @var Xtompie\Tpl\Tpl $this */ ?>`

## Requiments

PHP >= 8.0

## Installation

Using [composer](https://getcomposer.org/)

```shell
composer require xtompie/tpl
```

## Full feature example

```phtml

// Extend Tpl for customization, set templatePathPrefix, add helpers
// src/App/Shared/Tpl/Tpl.php

namespace App\Shared\Tpl\Tpl;

use Xtompie\Tpl\Tpl as BaseTpl;

class Tpl extends BaseTpl
{
    protected function templatePathPrefix(): string
    {
        return 'src/';
    }

    protected function date(int $time): string
    {
        return $this->e(date('Y-m-d H:i:s', $time));
    }
}

// src/App/Test/Ui/Controller/TestController.php

namespace App\Test\Ui\Controller;

use App\Shared\Tpl\Tpl;

class TestController
{
    public function __construct(
        private Tpl $tpl
    ) {
    }

    public function __invoke(): string
    {
        return $this->tpl->__invoke('Test/UI/Tpl/content.tpl.php', ['title' => 'foobar']);
    }
}

// src/Test/UI/Tpl/content.tpl.php - first level
// keep template file names with `.tpl.php` suffix e.g. easy exclude from phpstan
<?php /** @var App\Shared\Tpl\Tpl $this */ ?>
<?php $this->push('Test/UI/Tpl/layout.tpl.php', ['title' => $title]); ?>
<h1><?= $this->e($title) ?></h1>

// src/Test/UI/Tpl/layout.tpl.php - second level
<?php /** @var App\Shared\Tpl\Tpl $this */ ?>
<?php $this->push('Test/UI/Tpl/head.tpl.php', ['title' => $title]); ?>
<div class="container">
    <?= $this->render('Test/UI/Tpl/navbar.tpl.php') ?>
    <?= $this->content() ?>
</div>

// src/Test/UI/Tpl/navbar.tpl.php
<nav>
    <a href="/">Index</a>
</nav>

// src/Test/UI/Tpl/head.tpl.php - third level
<?php /** @var App\Shared\Tpl\Tpl $this */ ?>
<html>
    <head>
        <title><?= $this->e($title) ?></title>
    </head>
    <body>
        <?= $this->content() ?>
    </body>
</html>
```
