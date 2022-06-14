<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Sebasbit\LoadData\Csv\CsvReader;
use Sebasbit\LoadData\Db\DbQuery;
use Sebasbit\LoadData\Db\Statement\DynamicInsertStatement;

define('COMMAND_INFO', "
Command information
===================
Description:
    Insert data into a database table from a csv file.
Parameters:
    - <file> Path to csv file, the first line must be the columns headers
    - <dbhost> Database host and port
    - <dbname> Database name
    - <dbuser> User to open a connection
    - <dbpass> Password to open a connection
    - <dbtable> Table where the data will be inserted
Example:
    php load_data.php --file=data.csv --dbhost=localhost:3306 --dbname=example --dbuser=root --dbpass=root --dbtable=example
");

try {
    $file = get_option('file');
    $dbhost = get_option('dbhost');
    $dbname = get_option('dbname');
    $dbuser = get_option('dbuser');
    $dbpass = get_option('dbpass');
    $dbtable = get_option('dbtable');

    $dbQuery = new DbQuery($dbhost, $dbname, $dbuser, $dbpass);
    $csvReader = new CsvReader($file, ['headers' => true]);
} catch (Exception $e) {
    print_command_info(COMMAND_INFO, $e->getMessage());
}

echo "Starting load data from '$file' to $dbname.$dbtable\n\n";

$insertedRows = 0;

foreach ($csvReader as $rownum => $row) {
    // This method does not return a bool val, so the if statement
    // is using the inserted rows number.
    $inserted = $dbQuery->execute(new DynamicInsertStatement($dbtable, $row));

    if (!$inserted) {
        echo sprintf("Row %s was not inserted. Data: %s\n", $rownum, join(', ', $row));
        continue;
    }

    $insertedRows++;
}

echo "\nTotal rows inserted: $insertedRows\n\n";
