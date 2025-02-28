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

final class SQL extends File implements FileReaderInterface {

	protected string $file_path;
	protected array $readData = [];

	public function __construct(string $file_path)
	{
		if (!file_exists($file_path) || !is_readable($file_path)) {
			throw new Exception("File not found or not readable: " . htmlspecialchars($file_path));
		} 

		$this->file_path = $file_path;
	}


	public function read(): array 
	{
     $sqlContent = file_get_contents($this->file_path);

        if (!$sqlContent) {
            throw new Exception("SQL dosyası okunamadı veya boş.");
        }

        $this->readData = explode(";", $sqlContent);
        return $this->readData;
	}

	public function getColumns() : array 
	{
		return [];
	}

}

?>