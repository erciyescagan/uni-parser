<?php

namespace Merterciyescagan\UniParser;

interface FileReaderInterface {

	public function read(): Array;
	public function getColumns(): Array;
}

?>