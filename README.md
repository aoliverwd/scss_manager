![PHPUnit](https://github.com/aoliverwd/scss_manager/actions/workflows/ci.yml/badge.svg) [![Latest Stable Version](https://poser.pugx.org/alexoliverwd/scss_manager/v)](//packagist.org/packages/alexoliverwd/scss_manager) [![License](https://poser.pugx.org/alexoliverwd/brace/license)](//packagist.org/packages/alexoliverwd/scss_manager)

# SCSS Manager

This package is currently in alpha. Complete documentation will be added on official release.

## Usage

```php
use SCSSWrapper\Controller\Compiler

$compile = new Compiler([
    'db_location' => __DIR__ . '/assets.db'
]);

$compiled = $compile->compile([
    __DIR__ . '/scss/one.scss',
    __DIR__ . '/scss/two.scss'
], __DIR__ . '/compiled/homepage.css');
```