<?php

use Tests\TestCase;
use SCSSWrapper\Controller\Compiler;

test('Compile', function () {
    $paths = TestCase::getPaths();
    $compile = new Compiler([
        'db_location' => $paths->database
    ]);

    $compiled = $compile->compile([
        $paths->scss . 'one.scss',
        $paths->scss . 'two.scss'
    ], $paths->compiled . 'compiled.css');

    $compiled_file_exists = file_exists($compiled);
    TestCase::$compile_time = $compiled_file_exists ? filectime($compiled) : false;
    expect($compiled_file_exists)->toBeTrue();
});

test('Compile Already Existing', function () {
    $paths = TestCase::getPaths();
    $compile = new Compiler([
        'db_location' => $paths->database
    ]);

    sleep(1);

    $compiled = $compile->compile([
        $paths->scss . 'one.scss',
        $paths->scss . 'two.scss'
    ], $paths->compiled . 'compiled.css');

    $new_time = file_exists($compiled) ? filectime($compiled) : false;

    TestCase::cleanUp([
        $compiled,
        $paths->database
    ]);

    expect($new_time)->toBe(TestCase::$compile_time);
});
