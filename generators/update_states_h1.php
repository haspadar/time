<?php
require_once '../config.php';

$limit = 1000;
$offset = 0;
$count = DB::queryFirstField('SELECT COUNT(*) FROM urls WHERE city=""');
while ($cities = DB::query(
    'SELECT u.* FROM urls AS u WHERE u.city="" LIMIT %d OFFSET %d',
    $limit,
    $offset
)) {
    foreach ($cities as $key => $city) {
        echo ($key + 1) + $offset . '/' . $count . PHP_EOL;
        $isCountry = !$city['state'];
        $title = implode(', ', array_filter([$city['state'], $city['country']]));
        $updates = [
            'h1' => $isCountry ? $city['country'] : $title,
            'title' => $isCountry ? $city['country'] : $title,
//            'city' => $city,
            'admin1_code_ascii' => $city['admin1_code_ascii'] ?: ''
        ];
        var_dump($updates);
//        if ($city['url'] == 'CA') {
//            var_dump($updates);exit;
//        }
        Db::update('urls', $updates, 'id=%d', $city['id']);
    }

    $offset += $limit;
}
