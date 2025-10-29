<?php

declare(strict_types=1);

set_include_path(__DIR__ . '/../src' . PATH_SEPARATOR . get_include_path());

require_once __DIR__ . '/../src/File.php';

use Merterciyescagan\UniParser\File;

function assertEquals($expected, $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        $msg = $message !== '' ? $message . ' ' : '';
        throw new \Exception(
            $msg . 'Expected "' . var_export($expected, true) . '" but got "' . var_export($actual, true) . '".'
        );
    }
}

function assertTrue($condition, string $message = ''): void
{
    if (!$condition) {
        throw new \Exception($message !== '' ? $message : 'Assertion failed');
    }
}

try {
    $filePath = __DIR__ . '/fixtures/sample.tableu';
    $file = new File($filePath);

    $data = $file->read();
    assertEquals(2, count($data), 'Unexpected number of rows read from TABLEU file.');
    assertTrue(isset($data[0]['meta_notes']) && is_array($data[0]['meta_notes']), 'Nested data should be preserved for TABLEU rows.');

    $columns = $file->generateCreateTableString();
    assertEquals('CREATE TABLE sample (id VARCHAR(255), name VARCHAR(255), department VARCHAR(255), meta_notes VARCHAR(255));', $columns, 'CREATE TABLE statement mismatch for TABLEU file.');

    $insertSql = $file->generateBatchImportIntoString($data);
    $expectedInsert = "INSERT INTO sample (id, name, department, meta_notes) VALUES ('1', 'Alice', 'Engineering', '{\\\"level\\\":\\\"senior\\\"}'), ('2', 'Bob', 'Sales', NULL);";
    assertEquals($expectedInsert, $insertSql, 'INSERT statement mismatch for TABLEU file.');

    echo "All TABLEU tests passed\n";
    exit(0);
} catch (\Exception $exception) {
    fwrite(STDERR, 'TABLEU Test failure: ' . $exception->getMessage() . "\n");
    exit(1);
}
