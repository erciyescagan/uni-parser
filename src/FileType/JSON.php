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

final class JSON extends File implements FileReaderInterface {

	protected string $file_path;
    protected array $readData = [];

	public function __construct(string $file_path){
		if (!file_exists($file_path) || !is_readable($file_path)) {
			throw new Exception("File not found or not readable: ". htmlspecialchars($file_path));
		}
		$this->file_path = $file_path;
	}

  	public function read(): array {
    	$contents = file_get_contents($this->file_path);

    	if ($contents === false) {
    		throw new Exception("Failed to read file: ". htmlspecialchars($this->file_path));
    	}

    	$this->readData = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

    	if (!is_numeric(key($this->readData))) {
        	$newData = []; 
        	$index = 0;
        
        	foreach ($this->readData as $key => $readData) {
            	$readData["key"] = $key;
            	$newData[$index++] = $readData; 
        	}

        	$this->readData = $newData;
    	}
	    
        return is_array($this->readData) ? $this->readData : [];
	}

    public function getColumns(): array {

    	if (!$this->readData) {
    		$this->read();
    	}

    	return !empty($this->readData) ? array_keys(reset($this->readData)) : [];

        if (!is_numeric(key($content))) {
        	$index = 0;
        	$arrayKeys = array_keys($content[$key]);
        	$arrayKeys[] = "key";
        }

    	return $arrayKeys;
    }
 

}


	
?>