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