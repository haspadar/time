<?php use WhatTime\WhatTime;

if ($time->isCountry()) :?>
    <tr>
        <td class="prop_1"><img src="/img/flag.png" alt="Flag" class="icm2"/></td>
        <td class="prop_2">Capital:</td>
        <td class="prop_3">
            <a href="/<?=$time->getCountryCapital()->getUrl()?>">
                <?=$time->getFlag()?>
                <?=$time->getCountryCapital()->getCity()?>
            </a>
        </td>
    </tr>
<?php elseif ($time->isState()) :?>
    <tr>
        <td class="prop_1"><img src="/img/flag.png" alt="Flag" class="icm2"/></td>
        <td class="prop_2">Country:</td>
        <td class="prop_3">
            <a href="/<?=$time->getCountryUrl()?>">
                <?=$time->getFlag()?>
                <?=$time->getCountry()?>
            </a>
        </td>
    </tr>
    <tr>
        <td class="prop_1"><img src="/img/flag.png" alt="Flag" class="icm2"/></td>
        <td class="prop_2">Capital:</td>
        <td class="prop_3">
            <?=$time->getFlag()?>
            <?php $capital = $time->getStateCapital()?>
            <a href="/<?=$capital->getUrl()?>">
                <?=$capital->getCity()?>
            </a>
        </td>
    </tr>
<?php else :?>
    <tr>
        <td class="prop_1"><img src="/img/flag.png" alt="Flag" class="icm2"/></td>
        <td class="prop_2">Country:</td>
        <td class="prop_3">
            <a href="/<?=$time->getCountryUrl()?>">
                <?=$time->getFlag()?>
                <?=$time->getCountry()?>
            </a>

        </td>
    </tr>
<?php endif?>
<tr>
    <td class="prop_1"><img src="/img/earth.png" alt="Earth" class="icm2"/></td>
    <td class="prop_2">Time Zone:</td>
    <td class="prop_3">
        <?php foreach ($time->getTimezones() as $key => $timezone) :?>
            <?php $timezoneTime = (new DateTime())->setTimezone(new DateTimeZone($timezone))?>
            <?php if ($key) :?>
                <br>
            <?php endif;?>

            <span <?php if (!$key && count($time->getTimezones()) > 1): ?>class="active-timezone" <?php endif;?>>
                                            <?=IntlTimeZone::createTimeZone($timezone)->getDisplayName()?>
                                            (<?=$timezoneTime->format('T')?>)
                                            <?=$timezoneTime->format('O')?> UTC
                                        </span>
        <?php endforeach;?>
    </td>
</tr>
<tr>
    <td class="prop_1"><img src="/img/difference.png" alt="Difference" class="icm2"/></td>
    <td class="prop_2">Difference:</td>
    <?php $ipTime = WhatTime::generateIpTime(WhatTime::getCurrentIp())?>
    <?php $difference = WhatTime::getDifferenceInHours($ipTime, $time)?>
    <td class="prop_3"><?=$difference?> hour<?=$difference > 1 ? 's' : ''?> behind <a href="/<?=$ipTime->getUrl()?>"><?=$ipTime->getTitle()?></a></td>
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
    <td class="prop_1"><img src="/img/sunset.png" alt="Sunset" class="icm"/></td>
    <td class="prop_2">Sunset:</td>
    <td class="prop_3"><?=$time->getSunset()->format('H:i')?></td>
</tr>
<tr>
    <td class="prop_1"><img src="/img/sun.png" alt="Sun" class="icm"/></td>
    <td class="prop_2">Day length:</td>
    <td class="prop_3"><?=$time->getDayLength()?></td>
</tr>