<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public static function getPaths(): object
    {
        return (object) [
            'compiled' => __DIR__ . '/example-data/compiled/',
            'scss' => __DIR__ . '/example-data/scss/',
            'database' => __DIR__ . '/example-data/asstes.db'
        ];
    }

    public static function cleanUp(string $file_name): bool
    {
        if (file_exists($file_name)) {
            return unlink($file_name);
        }

        return false;
    }
}
