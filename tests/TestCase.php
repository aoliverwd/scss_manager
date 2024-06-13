<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public static int $compile_time;

    public static function getPaths(): object
    {
        return (object) [
            'compiled' => __DIR__ . '/example-data/compiled/',
            'scss' => __DIR__ . '/example-data/scss/',
            'database' => __DIR__ . '/example-data/asstes.db'
        ];
    }

    public static function cleanUp(array $file_names): void
    {
        array_map(function ($file_name) {
            if (file_exists($file_name)) {
                unlink($file_name);
            }
        }, $file_names);
    }
}
