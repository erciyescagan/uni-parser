<?php

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
        $this->file_path = $file_path;
    }
    public function read(): array {
        $data = [];

        if (($handle = fopen($this->file_path, "r")) !== FALSE) {
            $headers = fgetcsv($handle); 

            while (($row = fgetcsv($handle)) !== FALSE) {
           

                $data[] = array_combine($headers, $row); 


            }

            fclose($handle);
        }

        return $data;
    }

    public function getColumns(): array {
        if (($handle = fopen($this->file_path, "r")) !== FALSE) {
            $arrayKeys = fgetcsv($handle); 
            fclose($handle); 
        }
        return $arrayKeys;
    }




}

?>
