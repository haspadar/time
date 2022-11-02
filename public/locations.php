<?php
require_once '../config.php';
$search = trim(htmlspecialchars($_REQUEST['search']));
$cities = DB::query('SELECT * FROM urls WHERE title LIKE %s LIMIT 5', '%' . $search . '%');
$json = [];
foreach ($cities as $city) {
    $json[] = [
        'id' => $city['id'],
        'label' => $city['title'],
        'value' => $city['title'],
        'url' => $city['url'],
        'timezone' => $city['timezone']
    ];
}
echo json_encode($json);