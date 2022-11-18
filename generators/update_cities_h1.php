<?php
require_once '../config.php';

$limit = 1000;
$offset = 0;
$count = DB::queryFirstField('SELECT COUNT(*) FROM urls');
while ($cities = DB::query(
    'SELECT u.*, admin1_code, ac.ascii AS admin1_code_ascii FROM urls AS u INNER JOIN cities AS c ON u.city_id=c.id LEFT JOIN admin1_codes AS ac ON CONCAT(c.country_code, ".", c.admin1_code)=ac.code LIMIT %d OFFSET %d',
    $limit,
    $offset
)) {
    foreach ($cities as $key => $city) {
        echo ($key + 1) + $offset . '/' . $count . PHP_EOL;
        $isUs = $city['country'] == 'United States';
        $hasState = $isUs || !str_contains($city['admin1_code_ascii'], $city['city']) && $city['admin1_code_ascii'];
        if ($hasState) {
            $h1 = implode(', ', array_filter([$city['city'], $isUs ? $city['admin1_code'] : $city['admin1_code_ascii']]));
        } else {
            $h1 = implode(', ', array_filter([$city['city'], $city['country']]));
        }

        $title = implode(', ', array_filter([$city['city'], $hasState ? $city['admin1_code_ascii'] : '', $city['country']]));
        $updates = [
            'h1' => $h1,
            'title' => $isUs ? $title : $city['title'],
            'city' => $city,
            'admin1_code_ascii' => $city['admin1_code_ascii']
        ];
        Db::update('urls', $updates, 'id=%d', $city['id']);
    }

    $offset += $limit;
}
