# UniParser

**UniParser** is a PHP package that simplifies database integration by automatically parsing CSV, JSON, XLSX, XML, SQL, and Tableu (`.tableu`) files and generating `CREATE TABLE` and `INSERT INTO` SQL statements.

## Features

- **Multi-Format Support:** Easily parse CSV, JSON, XLSX, XML, SQL, and Tableu files.
- **Automatic SQL Generation:** Generate SQL queries for table creation and data insertion.
- **Flexible Usage:** Retrieve data as an array or generate SQL strings for MySQL integration.
- **Secure & Fast:** Designed with security in mind and optimized for performance.

## Installation

You can install UniParser via Composer:

```bash
composer require merterciyescagan/uniparser
```


```php
require 'vendor/autoload.php';

use Merterciyescagan\UniParser\File;

// Replace 'path/to/your/file.csv' with the actual path to your data file.
$file = new File('path/to/your/file.csv');

// Parse the file to get data as an array
$data = $file->read();

// Generate a CREATE TABLE SQL statement
$sqlCreate = $file->generateTableString();
echo $sqlCreate;

// Generate an INSERT INTO SQL statement for the parsed data
$sqlInsert = $file->generateBatchImportString($data);
echo $sqlInsert;
```

### Tableu files

UniParser expects `.tableu` files to contain JSON describing the dataset. You can either provide an object with a `columns` array and a `data` (or `rows`) array, or an array of row objects. A minimal example looks like:

```json
{
  "columns": ["id", "name", "department"],
  "data": [
    {"id": 1, "name": "Alice", "department": "Engineering"},
    {"id": 2, "name": "Bob", "department": "Sales"}
  ]
}
```

When the `columns` key is present, UniParser keeps the column order defined in the file. If it is omitted, the column names are inferred from the first row in the dataset.

## Contributing

**Contributions are welcome! If you have ideas, improvements, or bug fixes, feel free to open an issue or submit a pull request.**

## License

**UniParser is open-sourced software licensed under the MIT License.**
