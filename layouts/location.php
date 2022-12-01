<?php
use WhatTime\WhatTime;

require_once '../config.php';

$time = WhatTime::generateTime();

date_default_timezone_set($time->getTimezone());

$title = $time->getHtmlTitle();
$description = $time->getHtmlDescription();
require_once 'header.inc';

?>

<tr>
    <td class="content" align="center">
        <table class="tbl_content">
            <tbody>
            <tr>
                <td>
                    <h1 align="left"><?=$time->getHtmlH1()?></h1>
                    <div class="time"><?=$time->getDateTime()->format('H:i:s')?></div>
                    <div class="date" align="right"><?=$time->getDateTime()->format('l, d F Y')?></div>
                    <table class="properties" align="left">
                        <?php require_once 'properties.inc'?>
                    </table>
                    <?php $dstStartTime = $time->getDstStartTime()?>
                    <?php $dstEndTime = $time->getDstEndTime()?>

                    <table class="dst">
                        <tbody>
                        <?php if ($dstStartTime) :?>
                            <tr>
                                <td colspan="2" class="dst_title">Daylight Saving Time</td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="/img/dst-<?=intval($dstStartTime->format('H'))?>-<?=intval($dstStartTime->modify('+1 HOUR')->format('H'))?>.png" class="/img_dts" alt=""/>
                                </td>
                                <td>
                                    <img src="/img/dst-<?=intval($dstEndTime->format('H'))?>-<?=intval($dstEndTime->modify('-1 HOUR')->format('H'))?>.png" class="/img_dts" alt=""/>
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
                        <?php else :?>
                            <tr>
                                <td class="dst_title">Daylight Saving Time</td>
                            </tr>
                            <tr>
                                <td>This location does not observe Daylight Saving Time.</td>
                            </tr>
                        <?php endif;?>
                        </tbody>
                    </table>

                    <?php if ($time->getLatitude()) :?>
                        <div class="map">
                            <p>&nbsp;</p>
                            <p>&nbsp;</p>
                            <div id="map"
                                 data-latitude="<?=$time->getLatitude()?>"
                                 data-longitude="<?=$time->getLongitude()?>"
                                 data-accuracy="5"
                            ></div>
                        </div>
                    <?php endif;?>
                </td>
            </tr>
            </tbody>
        </table>
    </td>
</tr>

<?php require_once 'footer.inc';
