<?php

use Tests\TestCase;
use SCSSWrapper\Controllers\Compiler;

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
        $paths->database,
        $compiled
    ]);

    expect($new_time)->toBe(TestCase::$compile_time);
});

test('Compile Directory Location', function () {
    $paths = TestCase::getPaths();
    $compile = new Compiler([
        'db_location' => $paths->database
    ]);

    $compiled = $compile->compile([
        $paths->scss . 'one.scss',
        $paths->scss . 'two.scss'
    ], $paths->compiled);

    $compiled_file_exists = !is_dir($compiled) && file_exists($compiled);

    TestCase::cleanUp([
        $paths->database
    ]);

    if ($compiled_file_exists) {
        TestCase::cleanUp([
            $compiled
        ]);
    }

    expect($compiled_file_exists)->toBeTrue();
});

test('Force Refresh', function () {
    $paths = TestCase::getPaths();

    $compile = new Compiler([
        'db_location' => $paths->database,
        'forceRefresh' => true
    ]);

    $compiled = $compile->compile([
        $paths->scss . 'one.scss',
        $paths->scss . 'two.scss'
    ], $paths->compiled);

    $compiled_file_exists = !is_dir($compiled) && file_exists($compiled);

    TestCase::cleanUp([
        $paths->database
    ]);

    if ($compiled_file_exists) {
        TestCase::cleanUp([
            $compiled
        ]);
    }

    expect($compiled_file_exists)->toBeTrue();
});


test('Add Change Time', function () {
    $paths = TestCase::getPaths();

    $compile = new Compiler([
        'db_location' => $paths->database,
        'forceRefresh' => true,
        'addCtime' => true
    ]);

    $compiled = $compile->compile([
        $paths->scss . 'one.scss',
        $paths->scss . 'two.scss'
    ], $paths->compiled);

    $compiled_file_exists = !is_dir($compiled) && file_exists($compiled);
    $ctime_exists = preg_match('/\?ctime=[0-9]{10}/', $compiled);

    TestCase::cleanUp([
        $paths->database
    ]);

    if ($compiled_file_exists) {
        TestCase::cleanUp([
            $compiled
        ]);
    }

    expect(boolval($ctime_exists))->toBeTrue();
});
