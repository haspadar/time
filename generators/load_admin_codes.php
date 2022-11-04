<?php
require_once '../config.php';
$file_to_read = fopen('admin1CodesASCII.txt', 'r');
DB::query('TRUNCATE table admin1_codes');
if ($file_to_read !== FALSE) {
    $fields = [
        'code',
        'name',
        'ascii',
        'geonameid'
    ];
    $counter = 0;
    while (($line = fgets($file_to_read)) !== FALSE) {
        try {
            DB::insert('admin1_codes', array_combine($fields, explode("\t", $line)));
        } catch (Exception $e) {
            var_dump(explode("\t", $line));
            throw $e;
        }

        echo $counter++ . PHP_EOL;
    }
}
