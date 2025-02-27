<?php


namespace Erciyes\UniParser\FileType;

require_once __DIR__. "/../File.php";
require_once __DIR__. "/../FileReaderInterface.php";

use Erciyes\UniParser\FileReaderInterface;
use Erciyes\UniParser\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as PhpOfficeXLSXClass;
use Exception; 

final class XLSX extends File implements FileReaderInterface {
	
	protected String $file_path;
    protected array $readData = [];

	protected function __construct($file_path)
	{
		$this->file_path = $file_path;
	}

    public  function read(): array {
    	$this->readData = array_slice($this->readXLSXFile(), 1);
    	return $this->readData;
    }
    public function getColumns(): array {
    if (is_null($this->readData) || count($this->readData) == 0) {
    		$data = $this->readXLSXFile();
       }

    	$columns=  $data[0];
    	return $columns;
    }

    private function readXLSXFile(): array {
		$spreadsheet = IOFactory::load($this->file_path);
        $sheet = $spreadsheet->getActiveSheet();
        return $sheet->toArray();
    }

}

?>