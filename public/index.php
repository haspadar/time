<?php

require_once '../config.php';

$time = new \Time\Time(
    strtr(htmlentities($_SERVER['REQUEST_URI']), ['/' => '']),
    $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'])
);
date_default_timezone_set($time->getTimezone());
?>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <link rel="stylesheet" href="/jquery-ui.css">
    <link rel="stylesheet" href="/time.css">

    <title data-timezone="<?=$time->getTimezone()?>">
        <?php if ($time->getCity() || $time->getCountry()) :?>
            Time in <?=$time->getCity() ?: $time->getCountry()?>: <?=$time->getDateTime()->format('H:i')?>
        <?php else :?>
            Local time
        <?php endif;?>
    </title>
</head>
<body>
<input type="hidden" id="timezone" value="<?=$time->getTimezone()?>">
<input type="hidden" id="description" value="<?=$time->getDescription()?>">
<input type="text" class="location" placeholder="Location" value="<?=$time->getUrl() ? $time->getUrl()['title'] : ''?>">

    <div class="time">...</div>
    <div class="date">...</div>

<hr>
    Sunrise: <?=$time->getSunrise()->format('H:i')?>
    Sunset: <?=$time->getSunset()->format('H:i')?>
<hr>
    <ul>
    <?php foreach ([
            ['city' => 'Tokyo', 'timezone' => 'Asia/Tokyo'],
            ['city' => 'Beijing', 'timezone' => 'Asia/Shanghai'],
            ['city' => 'Kyiv', 'timezone' => 'Europe/Kiev'],
            ['city' => 'Paris', 'timezone' => 'Europe/Paris'],
            ['city' => 'London', 'timezone' => 'Europe/London'],
            ['city' => 'New York', 'timezone' => 'America/New_York'],
            ['city' => 'Los Angeles', 'timezone' => 'America/Los_Angeles'],
        ] as $location) :?>
        <li>Time in <?=$location['city']?>: <span class="location-time" data-timezone="<?=$location['timezone']?>">
                <?=$time->getDateTime()->format('H:i')?>
            </span>
        </li>
    <?php endforeach;?>
    </ul>

    <script src="/jquery.min.js"/></script>
    <script src="/jquery-ui.js"/></script>
    <script src="/jquery.ui.autocomplete.html.js"/></script>
    <script src="/time.js"/></script>
</body>
</html>
