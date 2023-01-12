<?php

namespace WhatTime\Sitemap;

use MaxMind\Db\Reader\Decoder;
use WhatTime\ExecutionTime;
use WhatTime\WhatTime;

class Xml
{
    private string $baseUrl;
    private int $filesCount;
    private string $domainUrl;


    public function __construct(string $domainUrl, int $fileUrlsLimit)
    {
        $this->domainUrl = $domainUrl;
        $this->baseUrl = $domainUrl . '/sitemaps/index.php';
        $this->fileUrlsLimit = $fileUrlsLimit;
        $urlsCount = (new Db())->getCount();
        $this->fileUrlsLimit = $fileUrlsLimit;
        $this->filesCount = ceil($urlsCount / $fileUrlsLimit);
    }

    public function generatePageFile($queryFileId): string
    {
        if ($queryFileId >= 1 && $queryFileId <= $this->filesCount) {
            $xml = new \SimpleXMLElement(
                '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>'
            );
            $urls = (new Db())->getUrls($this->fileUrlsLimit, $this->fileUrlsLimit * ($queryFileId - 1));
            foreach ($urls as $url) {
                $element = $xml->addChild('url');
                $element->addChild('loc', $this->domainUrl . $url['url']);
                $element->addChild('lastmod', (new \DateTime())->format('Y-m-d'));
                $element->addChild('changefreq', 'daily');
            }

            return $xml->asXML();
        } else {
            return '';
        }
    }

    public function generateIndexFile(): string
    {
        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>'
        );
        for ($fileId = 1; $fileId <= $this->fileUrlsLimit; $fileId++) {
            $element = $xml->addChild('url');
            $element->addChild('loc', $this->baseUrl . '?file=' . $fileId);
            $element->addChild('lastmod', (new \DateTime())->format('Y-m-d'));
            $element->addChild('changefreq', 'daily');
        }

        return $xml->asXML();
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