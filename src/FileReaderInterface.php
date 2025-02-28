<?php

/**
 * UniParser - A PHP package for parsing various data files and generating SQL statements.
 *
 * @license MIT
 * @copyright (c) [2025] [Mert Erciyes Çağan]
 */

namespace Merterciyescagan\UniParser;

interface FileReaderInterface {

	public function read(): Array;
	public function getColumns(): Array;
}

?>