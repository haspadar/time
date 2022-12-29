<?php

namespace WhatTime\Sitemap;

use MaxMind\Db\Reader\Decoder;
use WhatTime\ExecutionTime;
use WhatTime\WhatTime;

class Db
{
    /**
     * @var array
     */
    private array $limitedCountries;
    private int $count;


    public function __construct()
    {
        $this->count = 0;
        $this->limitedCountries = [
//            'Luxembourg',
//            'Singapore',
//            'Ireland',
//            'Switzerland',
//            'Norway',
            'United States',
//            'Hong Kong',
//            'Denmark',
//            'United Arab Emirates',
//            'San Marino',
//            'Netherlands',
//            'Taiwan',
//            'Iceland',
//            'Austria',
//            'Sweden',
//            'Germany',
//            'Australia',
//            'Belgium',
//            'Finland',
//            'Canada'
        ];
    }

    public function generate()
    {
        $executionTime = new ExecutionTime();
        $executionTime->start();
        $this->truncate();
        $this->save(['/compare', '/'], ['static']);
        $this->generateCombinations(
            WhatTime::getCitiesCount($this->limitedCountries),
            fn($limit, $offset) => WhatTime::getCities($this->limitedCountries, $limit, $offset),
            WhatTime::getUrlsCount($this->limitedCountries),
            fn($limit, $offset) => WhatTime::getUrls($this->limitedCountries, $limit, $offset),
            'city-combination'
        );
        $this->generateCombinations(
            WhatTime::getStatesCount(),
            fn($limit, $offset) => WhatTime::getStates($limit, $offset),
            WhatTime::getUrlsCount($this->limitedCountries),
            fn($limit, $offset) => WhatTime::getUrls($this->limitedCountries, $limit, $offset),
            'state-combination'
        );
        $this->generateCombinations(
            WhatTime::getCountriesCount(),
            fn($limit, $offset) => WhatTime::getCountries($limit, $offset),
            WhatTime::getUrlsCount($this->limitedCountries),
            fn($limit, $offset) => WhatTime::getUrls($this->limitedCountries, $limit, $offset),
            'country-combination'
        );
        $executionTime->end();
        $this->log('Generated sitemap in DB for ' . $executionTime->get());
        $this->log('Count: ' . $this->count);
    }

    private function generateCombinations(
        int $leftLocationsCount,
        callable $leftLocationsCallback,
        int $rightLocationsCount,
        callable $rightLocationsCallback,
        string $label
    ) {
        $limit = 1000;
        for ($leftLocationsOffset = 0; $leftLocationsOffset <= $leftLocationsCount; $leftLocationsOffset += $limit) {
            $leftLocations = $leftLocationsCallback($limit, $leftLocationsOffset);
            $this->save(
                array_map(fn($location) => '/' . $location['url'], $leftLocations),
                [$label, 'location']
            );
            foreach ($leftLocations as $leftLocation) {
                for ($rightLocationsOffset = 0; $rightLocationsOffset <= $rightLocationsCount; $rightLocationsOffset += $limit) {
                    $compares = array_filter(
                        $rightLocationsCallback($limit, $rightLocationsOffset),
                        fn($url) => $leftLocation['id'] != $url['id']
                    );
                    $this->save(
                        array_map(fn($location) => '/compare/' . $location['url'] . '/' . $leftLocation['url'], $compares),
                        [$label, 'combination', $leftLocation['url']]
                    );
                }
            }
        }
    }

    private function save(array $urls, array $labels): void
    {
        $this->log('Inserting ' . count($urls) . ' urls with labels ' . implode(', ', $labels) . '...');
        foreach ($urls as $url) {
            $this->count++;
            \DB::insert('sitemap', [
                'url' => $url,
                'label1' => $labels[0] ?? '',
                'label2' => $labels[1] ?? '',
                'label3' => $labels[2] ?? '',
                'create_time' => (new \DateTime())->format('Y-m-d H:i:s')
            ]);
        }
    }

    private function log(string $message): void
    {
        echo '[' . (new \DateTime())->format('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
    }

    private function truncate(): void
    {
        $this->log('Truncating table...');
        \DB::query('TRUNCATE table sitemap');
    }
}