<?php

use MaxMind\Db\Reader;

require_once '../config.php';

$requestLocation = strtr($_SERVER['REQUEST_URI'], ['/' => '']);
if ($requestLocation && $found = DB::queryFirstRow('SELECT * FROM cities WHERE ascii_name=%s', $requestLocation)) {
    $timezone = $found['timezone'];
    $description = $found['name'] . ', ' . $found['country_name_en'] . ', timezone ' . $timezone;
} elseif ($requestLocation && $found = DB::queryFirstRow('SELECT * FROM cities WHERE country_name_en=%s', $requestLocation)) {
    $timezone = $found['timezone'];
    $description = $found['country_name_en'] . ', timezone ' . $timezone;
} else {
    $reader = new Reader('../GeoLite2-City.mmdb');
    $ip = $_SERVER['HTTP_CLIENT_IP']
        ?? ($_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']);
    if (filter_var($ip, FILTER_VALIDATE_IP) && $ip != '::1') {
        $city = $reader->get($ip);
        $names = $city['city']['names'];
        $timezone = $city['location']['time_zone'];
        $cityName = $names[0] ?? $names['en'] ?? explode('/', $timezone)[1];
        $country = $city['country']['names']['en'] ?? $city['country']['names'][0] ?? '';
        $description = $cityName . ', ' . $country . ', timezone ' . $timezone . ', ip ' . $ip;
    } else {
        $timezone = 'Europe/Kiev';
        $description = explode('/', $timezone)[1];
    }
}


date_default_timezone_set($timezone);
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" href="/jquery-ui.css">
    <link rel="stylesheet" href="/time.css">

    <title>Update Time</title>
</head>
<body>
<input type="hidden" id="timezone" value="<?=$timezone?>">
<input type="hidden" id="description" value="<?=$description?>">
<input type="text" class="location" placeholder="Location" value="<?=$requestLocation ?? ''?>">

    <div class="time"></div>
    <div class="date"></div>
    <script src="/jquery.min.js"/></script>
    <script src="/jquery-ui.js"/></script>
    <script src="/time.js"/></script>
</body>
</html>
