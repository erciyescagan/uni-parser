<?php

namespace Merterciyescagan\UniParser;

require_once "FileReaderInterface.php";
require_once "FileType/CSV.php";
require_once "FileType/JSON.php";
require_once "FileType/XLSX.php";
require_once "FileType/XML.php";
require_once "FileType/SQL.php";


use Merterciyescagan\UniParser\FileReaderInterface;
use Merterciyescagan\UniParser\FileType\CSV;
use Merterciyescagan\UniParser\FileType\JSON;
use Merterciyescagan\UniParser\FileType\XLSX;
use Merterciyescagan\UniParser\FileType\XML;
use Merterciyescagan\UniParser\FileType\SQL;
use \Exception;

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
            'sql' => SQL::class
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
            throw new Exception("Columns is not set.");
        }

        $columnDefinitions = array_map(
            fn($column) => sprintf("%s VARCHAR(255)", 
                $this->sanitizeColumnName($column)), 
            $this->columns);
        $sql = "("; 

        return "(" . implode(", ", $columnDefinitions) . ")";


        foreach ($this->columns as $column) {
            if (is_null($column)) {
                die("Please don't leave any empty column names on your xlsx file.\n");
            }
            $column = !is_string($column) ? json_encode($column) :  $column;
            $sanitizedColumn = $this->sanitizeColumnName($column);
            $columnDefinitions[] = "$sanitizedColumn VARCHAR(255)";
        }
     
        $sql .= implode(", ", $columnDefinitions) . ")";
        return $sql;
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