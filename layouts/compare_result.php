<?php

use WhatTime\WhatTime;

require_once '../config.php';

$firstLocationTime = WhatTime::getFirstLocationTime();
$secondLocationTime = WhatTime::getSecondLocationTime();

$title = 'Time in ' . $firstLocationTime->getHtmlTitle() . ' vs ' . $secondLocationTime->getHtmlTitle();
$description = 'Check time difference between ' . $firstLocationTime->getHtmlDescription() . ' and ' . $secondLocationTime->getHtmlDescription();

require_once 'header.inc'; ?>

<tr>
    <td class="content" align="center">
        <table class="tbl_content">
            <tbody>
            <tr>
                <td>
                    <h1 align="left">Time in <?=$firstLocationTime->getHtmlTitle()?> vs <?=$secondLocationTime->getHtmlTitle()?></h1>
                    <table class="tbl_conv">
                        <tbody>
                        <tr>
                            <td class="country_vs"><?=$firstLocationTime->getFlag()?> <a href="<?=$firstLocationTime->getUrl()?>"><?=$firstLocationTime->getTitle()?></a></td>
                        </tr>
                        <tr>
                            <td class="time_vs"><?=$firstLocationTime->getDateTime()->format('H:i:s')?></td>
                        </tr>
                        <tr>
                            <td><?=$firstLocationTime->getDateTime()->format('l, d F Y')?></td>
                        </tr>
                        </tbody>
                    </table>
                    <table class="tbl_conv2">
                        <tbody>
                        <tr>
                            <td><?=$secondLocationTime->getFlag()?> <a href="<?=$secondLocationTime->getUrl()?>"><?=$secondLocationTime->getTitle()?></a></td>
                        </tr>
                        <tr>
                            <td class="time_vs"><?=$secondLocationTime->getDateTime()->format('H:i:s')?></td>
                        </tr>
                        <tr>
                            <td><?=$secondLocationTime->getDateTime()->format('l, d F Y')?></td>
                        </tr>
                        </tbody>
                    </table>
                    <p class="result_vs">
                        <?php
                        $hours = WhatTime::getDifferenceInHours($firstLocationTime, $secondLocationTime);
                        if ($hours > 0) :?>
                            <?=$firstLocationTime->getTitle()?> time is <span class="red"><?=$hours?>:00</span> hours behind <?=$secondLocationTime->getTitle()?>
                        <?php else :?>
                            <?=$firstLocationTime->getTitle()?> time is <span class="red"><?=-$hours?>:00</span> hours behind <?=$secondLocationTime->getTitle()?>
                        <?php endif; ?>

                    </p>
                    <table class="properties_vs">
                        <?php $time = $firstLocationTime;?>
                        <?php require 'properties.inc'?>
                    </table>
                    <table class="properties_vs2">
                        <?php $time = $secondLocationTime;?>
                        <?php require 'properties.inc'?>
                    </table>
            </tr>
            </tbody>
        </table>
    </td>
</tr>

<?php require_once 'footer.inc';
