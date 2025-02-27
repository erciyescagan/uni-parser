<?php


namespace Merterciyescagan\UniParser\FileType;

require_once __DIR__. "/../File.php";
require_once __DIR__. "/../FileReaderInterface.php";

use Merterciyescagan\UniParser\FileReaderInterface;
use Merterciyescagan\UniParser\File;
use Exception; 


final class XML extends File implements FileReaderInterface {

	protected String $file_path;
    protected array $readData = [];

	protected function __construct($file_path){
		$this->file_path = $file_path;
	}

	public  function read(): Array
	{
		 if (!file_exists($this->file_path)) {
            throw new \Exception("File not found: " . $this->file_path);
        }

        $xmlContent = simplexml_load_file($this->file_path, "SimpleXMLElement", LIBXML_NOCDATA);
        if (!$xmlContent) {
            throw new \Exception("Invalid XML format.");
        }

        $this->readData = $this->reaDXMLFile($xmlContent);
        return $this->readData;
    }

	public function getColumns(): Array {
	     if (empty($this->readData)) {
         	$this->readData = $this->read();
        }

        return array_keys($this->readXMLFile($this->readData));

        
	}


    private function readXMLFile($xml): array {
        $json = json_encode($xml);
        $array = json_decode($json, true);
        $key = key($array);
        return $array[$key] ?? [];
    }
}


?>