<?php
//Поиск 1904 пропущенных: SELECT * FROM cities WHERE id NOT IN(SELECT city_id FROM urls);

require_once 'config.php';
DB::query('TRUNCATE table urls');
$countriesWithSingleTimezone = DB::query(
    'SELECT country_name_en, COUNT(distinct timezone) AS count, GROUP_CONCAT(distinct timezone) AS timezone FROM cities WHERE country_name_en <> "" GROUP BY country_name_en having count=1'
);
foreach ($countriesWithSingleTimezone as $country) {
    $url  = str_replace(' ', '_', $country['country_name_en']);
    if (!DB::queryFirstRow('SELECT * FROM urls WHERE url = %s', $url)) {
        DB::insert('urls', [
            'country' => $country['country_name_en'],
            'timezone' => $country['timezone'],
            'url' => $url,
            'title' => generateTitle([$country['country_name_en']]),
        ]);
    }
}

$capitals = DB::query('SELECT id, name, country_name_en, timezone, admin1_code, country_code FROM cities WHERE feature_code="PPLC"');
foreach ($capitals as $capital) {
    $url = generateUrl([$capital['name']]);
    if (!($found = DB::queryFirstRow('SELECT * FROM urls WHERE url = %s', $url))) {
        DB::insert('urls', [
            'country' => $capital['country_name_en'],
            'timezone' => $capital['timezone'],
            'url' => $url,
            'title' => generateTitle([$capital['name'], $capital['country_name_en']]),
            'city_id' => $capital['id']
        ]);
    } elseif ($found['country'] != $capital['country_name_en']) {
        $url = generateAdminCodeUrl($capital);
        DB::insert('urls', [
            'country' => $capital['country_name_en'],
            'timezone' => $capital['timezone'],
            'url' => $url,
            'title' => generateAdminCodeTitle($capital),
            'city_id' => $capital['id']
        ]);
    }
}

$cities = DB::query('SELECT id, name, country_name_en, timezone, admin1_code, country_code FROM cities WHERE feature_code<>"PPLC"');
foreach ($cities as $city) {
    $url = generateAdminCodeUrl($city);
    if (!($found = DB::queryFirstRow('SELECT * FROM urls WHERE url = %s', $url))) {
        DB::insert('urls', [
            'country' => $city['country_name_en'],
            'timezone' => $city['timezone'],
            'title' => generateAdminCodeTitle($city),
            'url' => $url,
            'city_id' => $city['id']
        ]);
    } elseif ($found['country'] != $city['country_name_en']) {
        echo 'Ignored url ' . $url . ': already exists for ' . $found['country'] . PHP_EOL;
    }
}

function generateAdminCodeUrl($row): string {
    $code = $row['country_code'] . '.' . $row['admin1_code'];
    if ($foundCode = DB::queryFirstRow('SELECT * FROM admin1_codes WHERE code=%s', $code)) {
        return generateUrl([$row['name'], $foundCode['ascii'], $row['country_name_en']]);
    }

    return generateUrl([$row['name'], $row['country_name_en']]);
}

function generateAdminCodeTitle($row): string {
    $code = $row['country_code'] . '.' . $row['admin1_code'];
    if ($foundCode = DB::queryFirstRow('SELECT * FROM admin1_codes WHERE code=%s', $code)) {
        return generateTitle([$row['name'], $foundCode['ascii'], $row['country_name_en']]);
    }

    return generateTitle([$row['name'], $row['country_name_en']]);
}

function generateTitle(array $parts): string {
    return implode(', ', $parts);
}

function generateUrl(array $parts): string  {
    return strtr(implode('_', $parts), [' ' => '_']);
}