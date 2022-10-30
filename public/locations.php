<?php
require_once '../config.php';
$search = trim($_REQUEST['search']);
$cities = array_column(DB::query('SELECT * FROM cities WHERE ascii_name LIKE %s LIMIT 5', $search . '%'), 'ascii_name');
if ($cities) {
    echo json_encode($cities);
} else {
    $countries = array_column(DB::query('SELECT DISTINCT country_name_en FROM cities WHERE country_name_en LIKE %s LIMIT 5', $search . '%'), 'country_name_en');
    echo  json_encode($countries);
}
