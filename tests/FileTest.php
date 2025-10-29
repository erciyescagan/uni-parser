<?php

declare(strict_types=1);

set_include_path(__DIR__ . '/../src' . PATH_SEPARATOR . get_include_path());

require_once __DIR__ . '/../src/File.php';

use Merterciyescagan\UniParser\File;

function createFileInstanceWithColumns(array $columns): File
{
    $reflection = new \ReflectionClass(File::class);
    /** @var File $file */
    $file = $reflection->newInstanceWithoutConstructor();

    $columnsProperty = $reflection->getProperty('columns');
    $columnsProperty->setAccessible(true);
    $columnsProperty->setValue($file, $columns);

    return $file;
}

function invokeGenerateColumns(File $file): string
{
    $reflection = new \ReflectionClass(File::class);
    /** @var \ReflectionMethod $method */
    $method = $reflection->getMethod('generateColumnsArrayForSQLString');
    $method->setAccessible(true);

    return $method->invoke($file);
}

function assertEquals($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $msg = $message !== '' ? $message . ' ' : '';
        throw new \Exception($msg . 'Expected "' . $expected . '" but got "' . $actual . '".');
    }
}

function assertThrows(callable $callable, string $expectedMessage): void
{
    try {
        $callable();
    } catch (\Exception $exception) {
        if (strpos($exception->getMessage(), $expectedMessage) === false) {
            throw new \Exception(
                'Exception message did not contain expected text. Got: ' . $exception->getMessage()
            );
        }
        return;
    }

    throw new \Exception('Expected exception was not thrown.');
}

try {
    $file = createFileInstanceWithColumns(['id', 'name']);
    $result = invokeGenerateColumns($file);
    assertEquals('(id VARCHAR(255), name VARCHAR(255))', $result, 'Column definition mismatch.');

    $fileWithNull = createFileInstanceWithColumns(['id', null]);
    assertThrows(
        fn() => invokeGenerateColumns($fileWithNull),
        "Please don't leave any empty column names"
    );

    echo "All tests passed\n";
    exit(0);
} catch (\Exception $exception) {
    fwrite(STDERR, 'Test failure: ' . $exception->getMessage() . "\n");
    exit(1);
}
