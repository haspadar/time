<?php
require_once '../config.php';

$usStates = DB::query('SELECT ac.* FROM admin1_codes AS ac WHERE code IN (SELECT CONCAT(c.country_code, ".", c.admin1_code) FROM cities AS c WHERE country_code="US")');
foreach ($usStates as $usState) {
    $stateAbbr = explode('.', $usState['code'])[1];
    $countryAbbr = explode('.', $usState['code'])[0];
    if (($url = generateUrl([$stateAbbr]))
        && !($found = DB::queryFirstRow('SELECT * FROM urls WHERE url=%s', $url))
    ) {
        $stateCapital = DB::queryFirstRow('SELECT * FROM cities WHERE country_code=%s AND admin1_code=%s AND feature_code="PPLA"', $countryAbbr, $stateAbbr);
        if (!$stateCapital) {
            $stateCapital = DB::queryFirstRow('SELECT * FROM cities WHERE name="Washington" AND admin1_code="DC"');
        }

        $timezones = DB::queryFirstColumn('SELECT DISTINCT timezone FROM cities WHERE country_code=%s AND admin1_code=%s', $countryAbbr, $stateAbbr);
        DB::insert('urls', [
            'state' => $usState['ascii'],
            'country' => 'United States',
            'timezone' => implode(',', array_merge(
                [$stateCapital['timezone']],
                array_diff($timezones, [$stateCapital['timezone']])
            )),
            'title' => generateTitle([$usState['ascii'], 'United States']),
            'url' => $url,
            'coordinates' => $stateCapital['coordinates']
        ]);
        DB::update('urls', ['state' => $usState['ascii']], 'city_id = %d', $stateCapital['id']);
    } else {
        var_dump($found, 'Ignored same url');
    }
}


function generateTitle(array $parts): string {
    return implode(', ', $parts);
}

function generateUrl(array $parts): string  {
    return strtr(implode('_', $parts), [' ' => '_']);
}