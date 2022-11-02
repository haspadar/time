<?php

use WhatTime\WhatTime;

require_once '../config.php';

$time = WhatTime::generateTime();

date_default_timezone_set($time->getTimezone());
?>
<html xmlns="http://www.w3.org/1999/html" lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="/css/jquery-ui.css">
    <link rel="stylesheet" href="/css/whattime.css?t=<?=time()?>">
    <link rel="icon" type="image/png" href="/img/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Ubuntu">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.2/dist/leaflet.css" integrity="sha256-sA+zWATbFveLLNqWO2gtiw3HL/lh1giY/Inf1BJ0z14=" crossorigin=""/>

    <link rel="canonical" href="https://www.what-time.info/<?=$time->getCity() ?? $time->getCountry()?>>">
    <title>What time is it in <?=$time->getCity() ?: $time->getCountry()?></title>
    <meta name="description" content="Exact time now, time zone, time difference, sunrise/sunset time and key facts for Vilnius, Lithuania.">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<input type="hidden" id="timezone" value="<?=$time->getTimezone()?>">
<input type="hidden" id="description" value="<?=$time->getTitle()?>">
<table class="base">
    <tbody>
    <tr class="header">
        <td class="tdheader fifty" align="center"><table class="tbl_header">
                <tbody>
                <tr>
                    <td class="td_logo"><a href="/"><img src="/img/logo.png" alt="Time in your city" class="mylogo"/></a></td>
                    <td>
                        <input type="text" class="location" value="<?=$time->getTitle()?>" placeholder="Search for city or place...">
                    </td>
                </tr>
                </tbody>
            </table></td>
    </tr>
    <tr>
        <td class="content" align="center">
            <table class="tbl_content">
                <tbody>
                <tr>
                    <td>
                        <h1 align="left">Time in <?=$time->getCity()?> right now</h1>
                        <div class="time"><?=$time->getDateTime()->format('H:i:s')?></div>
                        <div class="date" align="right"><?=$time->getDateTime()->format('l, d F Y')?> in <?=$time->getTitle()?></div>
                        <table class="properties" align="left">
                            <tr>
                                <td class="prop_1"><img src="img/flag.png" alt="Flag" class="icm2"/></td>
                                <td class="prop_2">Country:</td>
                                <td class="prop_3"><?=$time->getFlag()?> <?=$time->getCountry()?></td>
                            </tr>
                            <tr>
                                <td class="prop_1"><img src="/img/earth.png" alt="Earth" class="icm2"/></td>
                                <td class="prop_2">Time Zone:</td>
                                <td class="prop_3">
                                    <?=IntlTimeZone::createTimeZone($time->getTimezone())->getDisplayName()?>
                                    (<?=($time->getDateTime()->format('T'))?>)
                                    +<?=($time->getDateTime()->format('O'))?> UTC
                                </td>
                            </tr>
                            <tr>
                                <td class="prop_1"><img src="/img/difference.png" alt="Difference" class="icm2"/></td>
                                <td class="prop_2">Difference:</td>
                                <?php $ipTime = WhatTime::generateIpTime(WhatTime::getCurrentIp())?>
                                <?php $difference = WhatTime::getDifferenceInHours($ipTime, $time)?>
                                <td class="prop_3"><?=$difference?> hour<?=$difference > 1 ? 's' : ''?> behind <a href="/<?=$ipTime->getUrl()?>>"><?=$ipTime->getTitle()?></a></td>
                            </tr>
                            <tr>
                                <td class="prop_1"><span class="prop_1"><img src="/img/location.png" alt="Location" class="icm2"/></span></td>
                                <td class="prop_2">Lat/Long:</td>
                                <td class="prop_3"><?=$time->getLatitudePercent()?> / <?=$time->getLongitude()?></td>
                            </tr>
                            <tr>
                                <td class="prop_1"><img src="/img/sunrise.png" alt="Sunrise" class="icm"/></td>
                                <td class="prop_2">Sunrise:</td>
                                <td class="prop_3"><?=$time->getSunrise()->format('H:i')?></td>
                            </tr>
                            <tr>
                                <td class="prop_1"><img src="img/sunset.png" alt="Sunset" class="icm"/></td>
                                <td class="prop_2">Sunset:</td>
                                <td class="prop_3"><?=$time->getSunset()->format('H:i')?></td>
                            </tr>
                            <tr>
                                <td class="prop_1"><img src="/img/sun.png" alt="Sun" class="icm"/></td>
                                <td class="prop_2">Day length:</td>
                                <td class="prop_3"><?=$time->getDayLength()?></td>
                            </tr>
                        </table>
                        <?php $dstStartTime = $time->getDstStartTime()?>
                        <?php $dstEndTime = $time->getDstEndTime()?>
                        <?php if ($dstStartTime) :?>
                            <table class="dst">
                            <tbody>
                            <tr>
                                <td colspan="2" class="dst_title">Daylight Saving Time</td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="/img/dst-<?=intval($dstStartTime->format('H'))?>-<?=intval($dstStartTime->modify('+1 HOUR')->format('H'))?>.png" class="img_dts" alt=""/>
                                </td>
                                <td>
                                    <img src="/img/dst-<?=intval($dstEndTime->format('H'))?>-<?=intval($dstEndTime->modify('-1 HOUR')->format('H'))?>.png" class="img_dts" alt=""/>
                                </td>
                            </tr>
                            <tr>
                                <td>Starts on</td>
                                <td>Ends on</td>
                            </tr>
                            <tr>
                                <td>
                                    <?php $dstStartTime = $time->getDstStartTime()?>
                                    <?php $dstEndTime = $time->getDstEndTime()?>
                                    <?=$dstStartTime->format('F d, Y')?><br>
                                    at <?=$dstStartTime->format('H:i')?>
                                </td>
                                <td><?=$dstEndTime->format('F d, Y')?><br>
                                    at <?=$dstEndTime->format('H:i')?></td>
                            </tr>
                            <tr>
                                <td>Set Your Clock<br>
                                    <strong class="red">Ahead 1 hour</strong></td>
                                <td>Set Your Clock<br>
                                    <strong class="red">Back 1 hour</strong></td>
                            </tr>
                            </tbody>
                        </table>
                        <?php endif;?>

                        <?php if ($time->getLatitude()) :?>
                            <div class="map">
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p>&nbsp;</p>
                                <p>
                                    <div id="map"
                                         data-latitude="<?=$time->getLatitude()?>"
                                         data-longitude="<?=$time->getLongitude()?>"
                                         data-accuracy="15"
                                    ></div>
                                </p>
                            </div>
                        <?php endif;?>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td class="td_footer" align="center"><a href="/" class="footer">What-time.info</a> - exact time for any city</td>
    </tr>
    </tbody>
</table>

<!--<input type="text" class="location" placeholder="Location" value="<?/*=$time->getTitle()*/?>">

    <div class="time">...</div>
    <div class="date">...</div>

<hr>
    Sunrise: <?/*=$time->getSunrise()->format('H:i')*/?>
    Sunset: <?/*=$time->getSunset()->format('H:i')*/?>
    Day Length: <?/*=$time->getDayLength()*/?>
    UTC offet: <?/*=$time->getUtcOffset()*/?> hours
    Timezone: <?/*=$time->getTimezone()*/?>
    <div><?/*=$time->getDstDescription()*/?></div>
<hr>
    <ul>
    <?php /*foreach ([
            ['city' => 'Tokyo', 'timezone' => 'Asia/Tokyo'],
            ['city' => 'Beijing', 'timezone' => 'Asia/Shanghai'],
            ['city' => 'Kyiv', 'timezone' => 'Europe/Kiev'],
            ['city' => 'Paris', 'timezone' => 'Europe/Paris'],
            ['city' => 'London', 'timezone' => 'Europe/London'],
            ['city' => 'New York', 'timezone' => 'America/New_York'],
            ['city' => 'Los Angeles', 'timezone' => 'America/Los_Angeles'],
        ] as $location) :*/?>
        <li>Time in <?/*=$location['city']*/?>: <span class="location-time" data-timezone="<?/*=$location['timezone']*/?>">
                <?/*=$time->getDateTime()->format('H:i')*/?>
            </span>
        </li>
    <?php /*endforeach;*/?>
    </ul>-->

    <script src="/js/jquery.min.js"/></script>
    <script src="/js/jquery-ui.js"/></script>
    <script src="/js/jquery.ui.autocomplete.html.js"/></script>
   <script src="https://unpkg.com/leaflet@1.9.2/dist/leaflet.js" integrity="sha256-o9N1jGDZrf5tS+Ft4gbIK7mYMipq9lqpVJ91xHSyKhg=" crossorigin=""></script>

    <script src="/js/whattime.js"/></script>
</body>
</html>
