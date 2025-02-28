<?php

/**
 * UniParser - A PHP package for parsing various data files and generating SQL statements.
 *
 * @license MIT
 * @copyright (c) [2025] [Mert Erciyes Çağan]
 */

namespace Merterciyescagan\UniParser\FileType;

require_once __DIR__. "/../File.php";
require_once __DIR__. "/../FileReaderInterface.php";

use Merterciyescagan\UniParser\FileReaderInterface;
use Merterciyescagan\UniParser\File;
use Exception; 

final class CSV extends File implements FileReaderInterface {

    protected string $file_path;
    protected array $readData = [];

    public function __construct(string $file_path) {
        if (!file_exists($file_path) || !is_readable($file_path)) {
            throw new Exception("File not found or not readable: ". htmlspecialchars($file_path));
        }

        $this->file_path = $file_path;
    }

    public function read(): array {
        $data = [];

        if (($handle = fopen($this->file_path, "r")) !== FALSE) {
            $headers = fgetcsv($handle); 
            if (!$headers) {
                throw new Exception("Invalid CSV format: missing headers.");
            }
            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) === count($headers)) {
                    $data[] = array_combine($headers, $row); 
                }   

            }

            fclose($handle);
        }

        return $data;
    }

    public function getColumns(): array {
        if (($handle = fopen($this->file_path, "r")) !== FALSE) {
            $arrayKeys = fgetcsv($handle); 
            fclose($handle); 
        } else {
            throw new Exception("Failed to open file: " . htmlspecialchars($this->file_path));
        }
        return $arrayKeys ?: [];
    }




}

?>
