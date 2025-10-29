<?php

/**
 * UniParser - A PHP package for parsing various data files and generating SQL statements.
 *
 * @license MIT
 * @copyright (c) [2025] [Mert Erciyes Çağan]
 */

namespace Merterciyescagan\UniParser;

require_once "FileReaderInterface.php";
require_once "FileType/CSV.php";
require_once "FileType/JSON.php";
require_once "FileType/XLSX.php";
require_once "FileType/XML.php";
require_once "FileType/SQL.php";
require_once "FileType/Tableu.php";


use Merterciyescagan\UniParser\FileReaderInterface;
use Merterciyescagan\UniParser\FileType\CSV;
use Merterciyescagan\UniParser\FileType\JSON;
use Merterciyescagan\UniParser\FileType\XLSX;
use Merterciyescagan\UniParser\FileType\XML;
use Merterciyescagan\UniParser\FileType\SQL;
use Merterciyescagan\UniParser\FileType\TABLEU;
use \Exception;
use \JsonException;

class File {
    protected string $file_path;
    protected array $readData = [];
    private string $tableName = "";
	protected FileReaderInterface $reader;
	private array $columns = [];

	public function __construct(string $file_path)
	{
        if (!file_exists($file_path) || !is_readable($file_path)) {
            throw new Exception("File not found or not readable: " . htmlspecialchars($file_path));
        }

    	$this->file_path = $file_path;
        $this->tableName = $this->generateTableName();
        $this->reader = $this->setReader();
        $this->columns = $this->reader->getColumns();
    }

    public function read(): array {
    	
    	return $this->readData = $this->reader->read();
    }


    private function setReader(): FileReaderInterface 
    {
        
     $extension = $this->getExtensionFromFilePath();

        $supportedFormats = [
            'csv'  => CSV::class,
            'json' => JSON::class,
            'xlsx' => XLSX::class,
            'xml'  => XML::class,
            'sql' => SQL::class,
            'tableu' => TABLEU::class
        ];

        if (!array_key_exists($extension, $supportedFormats)) {
            throw new Exception("Unsupported file type: " . htmlspecialchars($extension));
        }

        return new $supportedFormats[$extension]($this->file_path);

    }


    public function generateCreateTableString(): string 
    {
        if (empty($this->tableName)) {
            throw new Exception("table name is empty, cannot generate CREATE TABLE query.");
        }

        return sprintf("CREATE TABLE %s %s;", $this->tableName, $this->generateColumnsArrayForSQLString());

    }

    public function generateBatchImportIntoString($dataBatch): string 
    {

        if (empty($this->tableName)) {
            throw new Exception("Table name is not set.");
        }

        if (empty($dataBatch)) {
            throw new Exception("Data batch is empty.");
        }

        $columns = array_map([$this, 'sanitizeColumnName'], $this->columns);

        $sql = sprintf("INSERT INTO %s (%s) VALUES ", $this->tableName, implode(", ", $columns));

        $values = [];
        foreach ($dataBatch as $data) {
            $escapedValues = array_map(function ($value) {
                if (is_null($value)) {
                    return "NULL";
                }
                if (is_array($value)) {
                    $value = json_encode($value, JSON_THROW_ON_ERROR);
                }
                return "'" . addslashes($value) . "'";
            }, array_values($data));
        
            $values[] = "(" . implode(", ", $escapedValues) . ")";
        }

        return $sql . implode(", ", $values) . ";";
    
    }


    private function generateColumnsArrayForSQLString(): string
    {
        if (empty($this->columns)) {
            throw new Exception("Columns are not set.");
        }

        $columnDefinitions = [];

        foreach ($this->columns as $column) {
            if (is_null($column)) {
                throw new Exception("Please don't leave any empty column names on your xlsx file.");
            }

            if (!is_string($column)) {
                try {
                    $column = json_encode($column, JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    throw new Exception(
                        "Failed to encode column name to JSON: " . $exception->getMessage(),
                        0,
                        $exception
                    );
                }
            }

            $sanitizedColumn = $this->sanitizeColumnName($column);
            $columnDefinitions[] = sprintf("%s VARCHAR(255)", $sanitizedColumn);
        }

        return "(" . implode(", ", $columnDefinitions) . ")";
    }



    private function generateTableName(): string {

        $fileName = pathinfo($this->file_path, PATHINFO_FILENAME);
        return strtolower(preg_replace("/[^a-zA-Z0-9_]/", "_", trim($fileName, "_")));
    }

   
    private function sanitizeColumnName(?string $name): string {
        if (is_null($name)) {
            return bin2hex(random_bytes(8)); 
        }
        return preg_replace("/[^a-zA-Z0-9_]/", "_", trim($name));
    }

  
    private function getExtensionFromFilePath(): string
    {
        return strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION));
    }




}

?>