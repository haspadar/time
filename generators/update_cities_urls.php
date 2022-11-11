<?php
require_once '../config.php';

$limit = 1000;
$offset = 0;
$count = DB::queryFirstField('SELECT COUNT(*) FROM urls');
$duplicates = [];
while ($cities = DB::query(
    'SELECT u.*, admin1_code FROM urls AS u INNER JOIN cities AS c ON u.city_id=c.id LIMIT %d OFFSET %d',
    $limit,
    $offset
)) {
    foreach ($cities as $key => $city) {
        echo ($key + 1) + $offset . '/' . $count . PHP_EOL;
        if ($city['country_code'] == 'US') {
            $url = strtr($city['city'] . '_' . $city['admin1_code'], [' ' => '_']);
        } else {
            $url = $city['city'];
        }

        if ($url != $city['url'] && $found = DB::queryFirstRow('SELECT * FROM urls WHERE url=%s AND id<>%d', $url, $city['id'])) {
            $url = strtr($city['city'] . '_' . $city['country'], [' ' => '_']);
            $found = DB::queryFirstRow('SELECT * FROM urls WHERE url=%s AND id<>%d', $url, $city['id']);
        }

        if ($found ?? []) {
            $url .= '_' . strtr($city['city'] . '_' . $city['admin1_code'], [' ' => '_']);
            $found = DB::queryFirstRow('SELECT * FROM urls WHERE url=%s AND id<>%d', $url, $city['id']);
        }

        if ($found ?? []) {
            $url .= '_' . strtr($city['city'] . '_' . $city['admin1_code'] . '_' . $city['country'], [' ' => '_']);
            $found = DB::queryFirstRow('SELECT * FROM urls WHERE url=%s AND id<>%d', $url, $city['id']);
        }

        if ($found ?? []) {
            $duplicates[] = [
                'first' => $city,
                'second' => $found
            ];
            var_dump('Ignored duplicate');
        } else {
            DB::update('urls', ['url' => $url], 'id=%d', $city['id']);
        }
    }

    $offset += $limit;
}

var_dump($duplicates, '$duplicates');