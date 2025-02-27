<?php


namespace Merterciyescagan\UniParser\FileType;

require_once __DIR__. "/../File.php";
require_once __DIR__. "/../FileReaderInterface.php";

use Merterciyescagan\UniParser\FileReaderInterface;
use Merterciyescagan\UniParser\File;
use Exception; 

final class JSON extends File implements FileReaderInterface {

	protected string $file_path;
    protected array $readData = [];
    protected array $columns = [];
	public function __construct($file_path){
		$this->file_path = $file_path;
	}

  	public function read(): array {
    	$contents = file_get_contents($this->file_path, true);
    	$this->readData = json_decode($contents, true);

    	if (!is_numeric(key($this->readData))) {
        	$newData = []; 
        	$index = 0;
        
        	foreach ($this->readData as $key => $readData) {
            	$readData["key"] = $key;
            	$newData[$index++] = $readData; 
        	}

        	$this->readData = $newData;
    	}
	    
	    return $this->readData;
	}

    public function getColumns(): array {
        $content = json_decode(file_get_contents($this->file_path),true);
        $key = key($content);
        if (!is_numeric(key($content))) {
        	$index = 0;
        	$arrayKeys = array_keys($content[$key]);
        	$arrayKeys[] = "key";
        }

    	return $arrayKeys;
    }
 

}


	
?>