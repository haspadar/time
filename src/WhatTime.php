<?php

namespace WhatTime;

use http\Encoding\Stream\Debrotli;
use MaxMind\Db\Reader;

class WhatTime
{
    public static function getUrl(string $locationUrl): array
    {
        return \DB::queryFirstRow('SELECT * FROM urls WHERE url=%s', $locationUrl) ?? [];
    }

    public static function getCountryUrl(string $country): string
    {
        return \DB::queryFirstField('SELECT url FROM urls WHERE country=%s AND state="" AND city=""', $country) ?? '';
    }

    public static function generateTime(): Time
    {
        if ($requestLocation = strtr(Filter::get($_SERVER['REQUEST_URI']), ['/' => ''])) {
            $time = self::generateQueryTime($requestLocation);
        } elseif ($ip = self::getCurrentIp()) {
            $time = self::generateIpTime($ip);
        }

        if (!isset($time)) {
            $time = self::generateDefaultTime();
        }

        return $time;
    }

    public static function getCurrentIp(): string
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'] ?? ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR']);

        return filter_var($ip, FILTER_VALIDATE_IP) && $ip != '::1' ? $ip : '46.53.245.214';
    }

    public static function generateIpTime(string $ip): ?Time
    {
        $reader = new Reader('../GeoLite2-City.mmdb');
        $geoIp = $reader->get($ip);
        if ($geoIp) {
            $names = $geoIp['city']['names'];
            $timezone = strtr($geoIp['location']['time_zone'], [
                'Europe/Kyiv' => 'Europe/Kiev'
            ]);
            $cityName = $names[0] ?? $names['en'] ?? explode('/', $timezone)[1];
        } else {
            $timezone = 'Europe/Kiev';
            $cityName = 'Kiev';
        }

        if (($url = \DB::queryFirstRow('SELECT * FROM urls WHERE city=%s AND timezone=%s', $cityName, $timezone))
            || ($url = \DB::queryFirstRow('SELECT * FROM urls WHERE timezone=%s AND is_country_capital=1', $timezone))
            || ($url = \DB::queryFirstRow('SELECT * FROM urls WHERE timezone=%s', $timezone))
        ) {
            return new Time($url);
        } else {
            error_log('Undefined url by timezone="' . $timezone . '" AND city "' . $cityName . '"');

            return null;
        }
    }

    public static function getDifferenceInHours(Time $bigTime, Time $smallTime): int
    {
        return ($bigTime->getDateTime()->getOffset() - $smallTime->getDateTime()->getOffset()) / 3600;
    }

    public static function generateQueryTime(string $requestLocation): ?Time
    {
        $url = self::getUrl($requestLocation);

        return $url ? new Time($url) : null;
    }

    private static function generateDefaultTime(): Time
    {
        $url = \DB::queryFirstRow('SELECT * FROM urls WHERE country=%s AND is_country_capital=1', 'Ukraine');

        return new Time($url);
    }

    public static function getCountryCapital(array $url): Time
    {
        return new Time(\DB::queryFirstRow('SELECT * FROM urls WHERE country=%s AND is_country_capital=1', $url['country']));
    }

    public static function getStateCapital(array $url): Time
    {
        return new Time(\DB::queryFirstRow('SELECT * FROM urls WHERE state=%s AND is_state_capital=1', $url['state']));
    }

    public static function getFirstLocationTime(): Time
    {
        return new Time(self::getUrl(urldecode(self::getCompareUrls()[0])));
    }

    public static function getSecondLocationTime(): Time
    {
        return new Time(self::getUrl(urldecode(self::getCompareUrls()[1])));
    }

    private static function getCompareUrls(): array
    {
        return array_values(array_filter(explode('/', $_SERVER['REQUEST_URI'])));
    }
}