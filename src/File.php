<?php

namespace Erciyes\UniParser;

require_once "FileReaderInterface.php";
require_once "FileType/CSV.php";
require_once "FileType/JSON.php";
require_once "FileType/XLSX.php";
require_once "FileType/XML.php";


use Erciyes\UniParser\FileReaderInterface;
use Erciyes\UniParser\FileType\CSV;
use Erciyes\UniParser\FileType\JSON;
use Erciyes\UniParser\FileType\XLSX;
use Erciyes\UniParser\FileType\XML;
use \Exception;

class File {
    protected string $file_path;
    protected array $readData = [];
    private string $tableName = "";
	protected FileReaderInterface $reader;
	private array $columns = [];

	public function __construct($file_path)
	{
    	$this->file_path = $file_path;
        $this->tableName = $this->generateTableName();
        $this->reader = $this->setReader();
        $this->columns = $this->reader->getColumns();
    }

    public function read(): array {
    	
    	$this->readData = $this->reader->read();
    	return $this->readData;
    }


    private function setReader(): FileReaderInterface {
        
        $extension = $this->getExtensionFromFilePath();

        $supportedFormats = [
            'csv'  => CSV::class,
            'json' => JSON::class,
            'xlsx' => XLSX::class,
            'xml'  => XML::class,
        ];

        if (!array_key_exists($extension, $supportedFormats)) {
            throw new Exception("Unsupported file type: $extension");
        }

        $className = $supportedFormats[$extension];

        $this->reader = new $className($this->file_path);

        return $this->reader;

    }


    public function generateTableString(): string {
        if (empty($this->tableName)) {
            throw new Exception("table name is empty, cannot generate CREATE TABLE query.");
        }

        $sql = "CREATE TABLE $this->tableName";

        $sql .= $this->generateColumnsArrayForSQLString() . ";";

        return $sql;
    }

    public function generateBatchImportString($dataBatch): string 
    {

        if (empty($this->tableName)) {
            throw new Exception("Table name is not set.");
        }

        if (empty($dataBatch)) {
            throw new Exception("Data batch is empty.");
        }

        $columns = $this->columns;
        $sql = "INSERT INTO $this->tableName (" . implode(", ", array_map([$this, 'sanitizeColumnName'], $columns)) . ") VALUES ";
    
        $values = [];
        foreach ($this->readData as $data) {
            $escapedValues = array_map(function ($value) {
                $value = is_null($value) ? "" : $value;
                $value = !is_string($value) && count($value) > 0 ? json_encode($value) : $value;
                return is_numeric($value) ? $value : "'" . addslashes($value) . "'";
            }, array_values($data));
        
            $values[] = "(" . implode(", ", $escapedValues) . ")";
        }

        $sql .= implode(", ", $values) . ";";
    
        return $sql;
    }


    private function generateColumnsArrayForSQLString(): string {
        if (empty($this->columns)) {
            throw new Exception("Columns is not set.");
        }
        $columnDefinitions = [];
        $sql = "("; 
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
        $fileName = basename($this->file_path, "." . pathinfo($this->file_path, PATHINFO_EXTENSION)); 

        $fileName = preg_replace("/[^a-zA-Z0-9_]/", "_", $fileName); 
        return "`" . strtolower(trim($fileName, "_")) . "`"; 
    }

   
    private function sanitizeColumnName(string $name): string {
        $name = preg_replace("/[^a-zA-Z0-9_]/", "_", $name); 
        return "`" . trim($name, "_") . "`"; 
    }

  
    private function getExtensionFromFilePath(): string
    {
        $extension = strtolower(trim(pathinfo($this->file_path, PATHINFO_EXTENSION)));
        return $extension;
    }




}

?>