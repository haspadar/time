<?php
require_once '../config.php';

$countries = DB::queryFirstColumn('SELECT DISTINCT country_name_en FROM cities AS c WHERE country_name_en NOT IN(SELECT url FROM urls) AND REPLACE(country_name_en, " ", "_") NOT IN(SELECT url FROM urls) AND country_name_en <>""');
foreach ($countries as $country) {
    $url = strtr($country, [' ' => '_']);
//    $found = DB::queryFirstRow('SELECT * FROM urls WHERE url=%s', $url);
    $capital = DB::queryFirstRow('SELECT * FROM urls WHERE country=%s AND is_country_capital=1', $country);
    if (!$capital) {
        var_dump($country, 'not found');
    }

    DB::insert('urls', [
        'url' => $url,
        'country' => $country,
        'title' => $country,
        'coordinates' => $capital['coordinates'],
        'timezone' => $capital['timezone'],
    ]);
    var_dump('Added ' . $url);
}

