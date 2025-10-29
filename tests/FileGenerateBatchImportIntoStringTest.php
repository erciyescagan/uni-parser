<?php

declare(strict_types=1);

use Merterciyescagan\UniParser\File;
use PHPUnit\Framework\TestCase;

class FileGenerateBatchImportIntoStringTest extends TestCase
{
    public function testGenerateBatchImportIntoStringFormatsValues(): void
    {
        $file = $this->createFileWithMetadata('sample_table', ['name', 'details', 'notes']);

        $dataBatch = [
            [
                'name' => "O'Reilly",
                'details' => ['foo' => 'bar'],
                'notes' => null,
            ],
        ];

        $sql = $file->generateBatchImportIntoString($dataBatch);

        $expected = "INSERT INTO sample_table (name, details, notes) VALUES ('O\\'Reilly', '{\\\"foo\\\":\\\"bar\\\"}', NULL);";

        $this->assertSame($expected, $sql, 'Generated SQL did not match expectations.');
    }

    /**
     * @return File
     */
    private function createFileWithMetadata(string $tableName, array $columns): File
    {
        $reflection = new \ReflectionClass(File::class);
        /** @var File $file */
        $file = $reflection->newInstanceWithoutConstructor();

        $tableNameProperty = $reflection->getProperty('tableName');
        $tableNameProperty->setAccessible(true);
        $tableNameProperty->setValue($file, $tableName);

        $columnsProperty = $reflection->getProperty('columns');
        $columnsProperty->setAccessible(true);
        $columnsProperty->setValue($file, $columns);

        return $file;
    }
}
