<?php

namespace WhatTime;

class Sitemap
{
    const MAX_FILE_LINKS = 40000;
    const MAX_FILE_SIZE_MB = 40;
    private string $domainUrl;
    private string $path;

    public function __construct(string $domainUrl, string $path)
    {
        $this->domainUrl = $domainUrl;
        $this->path = $path;
    }

    public function generate()
    {
        $executionTime = new ExecutionTime();
        $executionTime->start();
        $this->generateStaticFile([
            $this->domainUrl,
            $this->domainUrl . '/compare'
        ], $this->path . '/static');
//        $this->generateLocationCombinationUrls(
//            WhatTime::getCitiesCount(),
//            fn($limit, $offset) => WhatTime::getCities($limit, $offset),
//            $this->path . '/cities'
//        );
        $this->generateLocationCombinationUrls(
            WhatTime::getStatesCount(),
            fn($limit, $offset) => WhatTime::getStates($limit, $offset),
            $this->path . '/states'
        );
        $this->generateLocationCombinationUrls(
            WhatTime::getCountriesCount(),
            fn($limit, $offset) => WhatTime::getCountries($limit, $offset),
            $this->path . '/countries'
        );
        $siteMapIndexUrl = $this->generateIndexes();
        $executionTime->end();
        $this->log('Generated sitemap ' . $siteMapIndexUrl . ' for ' . $executionTime->get());
    }

    private function generateStaticFile(array $urls, string $path)
    {
        $chunks = array_chunk($urls, $this->getMaxFileLinks());
        foreach ($chunks as $chunkKey => $chunk) {
            $this->saveUrls($path, $chunkKey + 1, $chunk);
        }

        $this->saveUrls($path, $chunkKey + 1, $chunk);
    }

    private function generateLocationCombinationUrls(int $locationsCount, $locationsCallback, string $path)
    {
        $urlsCount = WhatTime::getUrlsCount();
        $limit = 1000;
        for ($locationsOffset = 0; $locationsOffset <= $locationsCount; $locationsOffset++) {
            $locations = $locationsCallback($limit, $locationsOffset);
            $this->generateLocationFiles($locations, $path);
            foreach ($locations as $location) {
                for ($urlsOffset = 0; $urlsOffset <= min($urlsCount - $limit, 0); $urlsOffset++) {
                    $compares = array_filter(
                        WhatTime::getUrls($limit, $urlsOffset),
                        fn($url) => $location['id'] != $url['id']
                    );
                    $this->generateComparesFiles($location, $compares, $path . '/' . $location['url'] . '_compares');
                }
            }
        }
    }

    private function generateComparesFiles(array $location, array $compares, string $path)
    {
        $urls = [];
        foreach ($compares as $compare) {
            $urls[] = $this->domainUrl . '/' . $location['url'] . '/' . $compare['url'];
        }

        $chunks = array_chunk($urls, $this->getMaxFileLinks());
        foreach ($chunks as $chunkKey => $chunk) {
            $this->saveUrls($path, $chunkKey + 1, $chunk);
        }
    }

    private function generateLocationFiles(array $locations, string $path)
    {
        $urls = [];
        foreach ($locations as $location) {
            $urls[] = $this->domainUrl . '/' . $location['url'];
        }

        $chunks = array_chunk($urls, $this->getMaxFileLinks());
        foreach ($chunks as $chunkKey => $chunk) {
            $this->saveUrls($path, $chunkKey + 1, $chunk);
        }
    }

    private function saveUrls(string $path, string $fileName, array $urls, bool $checkSize = true)
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $xml = new \SimpleXMLElement(
            '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>'
        );
        foreach ($urls as $url) {
            $element = $xml->addChild('url');
            $element->addChild('loc', $this->domainUrl . $url);
            $element->addChild('lastmod', (new \DateTime())->format('Y-m-d'));
            $element->addChild('changefreq', 'daily');
        }

        $fullFilePath = $path . '/' . $fileName . '.xml';
        $xml->saveXML($fullFilePath);
        $this->log('Saved ' . count($urls) . ' urls to ' . $fullFilePath);
        if ($checkSize) {
            if ($this->getFileSizeInMb($fullFilePath) > $this->getMaxFileSizeMb()) {
                $chunksCount = ceil($this->getFileSizeInMb($fullFilePath) / $this->getMaxFileSizeMb());
                $chunks = array_chunk($urls, ceil(count($urls) / $chunksCount));
                foreach ($chunks as $chunkKey => $chunk) {
                    $this->saveUrls($path, $fileName . '-' . ($chunkKey + 1), $chunk);
                }

                $this->log('Split file ' . $fullFilePath . ' to ' . $chunksCount . ' files');
                unlink($fullFilePath);
                $this->log('Removed ' . $fullFilePath . ' file');
            }
        }
    }

    private function getFileSizeInMb(string $file): float
    {
        $filesize = filesize($file); // bytes

        return round($filesize / 1024 / 1024, 4); // megabytes with 1 digit
    }

    private function getDirectoryRecursiveFiles(string $directory, &$results = array()): array
    {
        $files = scandir($directory);
        foreach ($files as $value) {
            $path = realpath($directory . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getDirectoryRecursiveFiles($path, $results);
            }
        }

        return $results;
    }

    private function getDirectoryFiles(string $directory): array
    {
        $files = scandir($directory);
        $results = [];
        foreach ($files as $value) {
            $path = realpath($directory . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            }
        }

        return $results;
    }

    private function generateIndexes(): string
    {
        $fileNameParts = array_values(array_filter(explode('/', $this->path)));
        $fileName = $fileNameParts[count($fileNameParts) - 1];
        $filesUrls = $this->getFilesUrls(
            $this->getDirectoryRecursiveFiles($this->path),
            ['/' . $fileName . '.xml']
        );

        $this->saveUrls($this->path, $fileName, $filesUrls);
        $indexesUrls = $this->getFilesUrls(
            $this->getDirectoryFiles($this->path),
            ['/' . $fileName . '.xml']
        );
        if (count($indexesUrls) > 1) {
            $this->saveUrls($this->path, $fileName, $indexesUrls, false);
        }

        $siteMapIndexUrl = $this->path . '/' . $fileName . '.xml';

        return $siteMapIndexUrl;
    }

    private function getFilesUrls(array $paths, array $excludes = []): array
    {
        $filesUrls = [];
        foreach ($paths as $path) {
            $parts = explode($this->path, $path);
            if (!in_array($parts[1], $excludes)) {
                $filesUrls[] = $this->path . $parts[1];
            }
        }

        return $filesUrls;
    }

    private function getMaxFileLinks(): int
    {
        return self::MAX_FILE_LINKS;
    }

    private function getMaxFileSizeMb(): float
    {
        return self::MAX_FILE_SIZE_MB;
    }

    private function log(string $message): void
    {
        echo '[' . (new \DateTime())->format('Y-m-d H:i:s') . '] ' . $message . PHP_EOL; 
    }
}