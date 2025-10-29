<?php

/**
 * UniParser - A PHP package for parsing various data files and generating SQL statements.
 *
 * @license MIT
 * @copyright (c) [2025] [Mert Erciyes Çağan]
 */

namespace Merterciyescagan\UniParser\FileType;

require_once __DIR__ . '/../File.php';
require_once __DIR__ . '/../FileReaderInterface.php';

use Merterciyescagan\UniParser\File;
use Merterciyescagan\UniParser\FileReaderInterface;
use Exception;
use JsonException;

final class TABLEU extends File implements FileReaderInterface
{
    protected string $file_path;
    protected array $readData = [];
    private array $columns = [];

    public function __construct(string $file_path)
    {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            throw new Exception('File not found or not readable: ' . htmlspecialchars($file_path));
        }

        $this->file_path = $file_path;
    }

    public function read(): array
    {
        if (!empty($this->readData)) {
            return $this->readData;
        }

        $contents = file_get_contents($this->file_path);
        if ($contents === false) {
            throw new Exception('Failed to read file: ' . htmlspecialchars($this->file_path));
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new Exception('Failed to decode TABLEU file: ' . $exception->getMessage(), 0, $exception);
        }

        if (!is_array($decoded)) {
            throw new Exception('Invalid TABLEU file: expected a JSON object or array.');
        }

        $rows = $this->extractRows($decoded);
        $normalizedRows = $this->normaliseRows($rows);

        if (empty($this->columns) && !empty($normalizedRows)) {
            $firstRow = reset($normalizedRows);
            $this->columns = is_array($firstRow) ? array_keys($firstRow) : [];
        }

        $this->readData = $normalizedRows;

        return $this->readData;
    }

    public function getColumns(): array
    {
        if (!empty($this->columns)) {
            return $this->columns;
        }

        if (empty($this->readData)) {
            $this->read();
        }

        $firstRow = $this->readData[0] ?? [];
        $this->columns = is_array($firstRow) ? array_keys($firstRow) : [];

        return $this->columns;
    }

    /**
     * @param array $decoded
     * @return array
     */
    private function extractRows(array $decoded): array
    {
        if (isset($decoded['data']) && is_array($decoded['data'])) {
            $rows = $decoded['data'];
        } elseif (isset($decoded['rows']) && is_array($decoded['rows'])) {
            $rows = $decoded['rows'];
        } else {
            $rows = $decoded;
        }

        if (isset($decoded['columns']) && is_array($decoded['columns'])) {
            $this->columns = $decoded['columns'];
        }

        return $rows;
    }

    /**
     * @param array $rows
     * @return array
     */
    private function normaliseRows(array $rows): array
    {
        $normalised = [];
        foreach ($rows as $rowIndex => $row) {
            if (!is_array($row)) {
                throw new Exception('Invalid TABLEU file: row ' . $rowIndex . ' is not an object or array.');
            }

            if (!empty($this->columns)) {
                $row = $this->alignRowWithColumns($row);
            }

            $normalised[] = $row;
        }

        return $normalised;
    }

    private function alignRowWithColumns(array $row): array
    {
        $orderedRow = [];
        foreach ($this->columns as $column) {
            $orderedRow[$column] = $row[$column] ?? null;
        }

        foreach ($row as $key => $value) {
            if (!in_array($key, $this->columns, true)) {
                $orderedRow[$key] = $value;
            }
        }

        return $orderedRow;
    }
}

?>
