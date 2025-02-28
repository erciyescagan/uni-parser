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

  public function __construct(string $file_path) {
    if (!file_exists($file_path) || !is_readable($file_path)) {
        throw new Exception("File not found or not readable: ". htmlspecialchars($file_path));
    }

    $this->file_path = $file_path;
  
  }


	public  function read(): Array
	{
		$xmlContent = simplexml_load_file($this->file_path, "SimpleXMLElement", LIBXML_NOCDATA);
        
        if (!$xmlContent) {
            throw new \Exception("Invalid XML format.");
        }
        return json_decode(json_encode($xmlContent), true) ?: [];

        $this->readData = $this->reaDXMLFile($xmlContent);

        return $this->readData;
    }

	public function getColumns(): Array 
    {
	     if (empty($this->readData)) {
         	$this->readData = $this->read();
        }

        return !empty($this->readData) && is_array($this->readData) ? array_keys($this->readData) : [];
        
	}

}


?>