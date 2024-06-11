<?php

use Tests\TestCase;
use SCSSWrapper\Controller\Compiler;

test('compile', function () {
    $paths = TestCase::getPaths();
    $compile = new Compiler([
        'db_location' => $paths->database
    ]);

    $compiled = $compile->compile([
        $paths->scss . 'one.scss'
    ], $paths->compiled . 'compiled.css');

    $file_compiled = TestCase::cleanUp($compiled);
    expect($file_compiled)->toBeTrue();
});
