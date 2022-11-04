<?php
require_once '../config.php';
$file_to_read = fopen('geonames-all-cities-with-a-population-1000.csv', 'r');
DB::query('TRUNCATE table cities');
if ($file_to_read !== FALSE) {
    $fields = [
        'geoname_id',
        'name',
        'ascii_name',
        'alternate_names',
        'feature_class',
        'feature_code',
        'country_code',
        'country_name_en',
        'country_code_2',
        'admin1_code',
        'admin2_code',
        'admin3_code',
        'admin4_code',
        'population',
        'elevation',
        'digital_elevation_model',
        'timezone',
        'modification_date',
        'label_en',
        'coordinates'
    ];
    $counter = 0;
    while (($line = fgetcsv($file_to_read, 10000, ';')) !== FALSE) {
        if ($line[0] != 'Geoname ID') {
            try {
                DB::insert('cities', array_combine($fields, $line));
            } catch (Exception $e) {
                var_dump(array_combine($fields, $line));
                throw $e;
            }

            echo $counter++ . '/140671' . PHP_EOL;
        }
    }
}
