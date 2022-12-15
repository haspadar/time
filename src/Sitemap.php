<?php

namespace WhatTime;

use http\Env\Request;

class Sitemap
{
    const MAX_FILE_LINKS = 40000;
    const MAX_FILE_SIZE_MB = 40;
    private string $domainUrl;
    private string $path;
    private string $tmpPath;

    public function __construct(string $domainUrl, string $path)
    {
        $this->domainUrl = $domainUrl;
        $this->path = $path;
        $this->tmpPath = $path . '-tmp';
    }

    public function generate()
    {
        $executionTime = new ExecutionTime();
        $executionTime->start();
        $this->removeTmpDirectory();
        $this->generateStaticFile([
            $this->domainUrl,
            $this->domainUrl . '/compare'
        ], $this->tmpPath . '/static');
        $this->generateLocationCombinationUrls(
            WhatTime::getCitiesCount(),
            fn($limit, $offset) => WhatTime::getCities($limit, $offset),
            WhatTime::getUrlsCount(),
            fn($limit, $offset) => WhatTime::getUrls($limit, $offset),
            $this->tmpPath . '/cities'
        );
        $this->generateLocationCombinationUrls(
            WhatTime::getStatesCount(),
            fn($limit, $offset) => WhatTime::getStates($limit, $offset),
            WhatTime::getUrlsCount(),
            fn($limit, $offset) => WhatTime::getUrls($limit, $offset),
            $this->tmpPath . '/states'
        );
        $this->generateLocationCombinationUrls(
            WhatTime::getCountriesCount(),
            fn($limit, $offset) => WhatTime::getCountries($limit, $offset),
            WhatTime::getUrlsCount(),
            fn($limit, $offset) => WhatTime::getUrls($limit, $offset),
            $this->tmpPath . '/countries'
        );
        $siteMapIndexUrl = $this->generateIndexes();
        $this->renameDirectories();
        $executionTime->end();
        $this->log('Generated sitemap ' . $siteMapIndexUrl . ' for ' . $executionTime->get());
    }

    private function generateStaticFile(array $urls, string $path)
    {
        $this->log('Generating1 static file ' . $path);
        $chunks = array_chunk($urls, $this->getMaxFileLinks());
        foreach ($chunks as $chunkKey => $chunk) {
            $this->saveUrls($path, $chunkKey + 1, $chunk);
        }

        $this->saveUrls($path, $chunkKey + 1, $chunk);
    }

    private function generateLocationCombinationUrls(
        int $leftLocationsCount,
        callable $leftLocationsCallback,
        int $rightLocationsCount,
        callable $rightLocationsCallback,
        string   $path
    ) {
        $limit = 1000;
        $locationsChunkId = 0;
        for ($leftLocationsOffset = 0; $leftLocationsOffset <= $leftLocationsCount; $leftLocationsOffset += $limit) {
            $leftLocations = $leftLocationsCallback($limit, $leftLocationsOffset);
            $this->generateLocationFiles($leftLocations, $path, ++$locationsChunkId);
            foreach ($leftLocations as $leftLocation) {
                $comparesChunkId = 0;
                for ($rightLocationsOffset = 0; $rightLocationsOffset <= $rightLocationsCount; $rightLocationsOffset += $limit) {
                    $compares = array_filter(
                        $rightLocationsCallback($limit, $rightLocationsOffset),
                        fn($url) => $leftLocation['id'] != $url['id']
                    );
                    $this->generateComparesFiles($leftLocation, $compares, $path . '/' . $leftLocation['url'] . '_compares', ++$comparesChunkId);
                }
            }
        }
    }

    private function generateComparesFiles(array $location, array $compares, string $path, int $chunkId)
    {
        $urls = [];
        foreach ($compares as $compare) {
            $urls[] = $this->domainUrl . '/' . $location['url'] . '/' . $compare['url'];
        }

        $this->saveUrls($path, $chunkId, $urls);
    }

    private function generateLocationFiles(array $locations, string $path, int $chunkId)
    {
        $urls = [];
        foreach ($locations as $location) {
            $urls[] = $this->domainUrl . '/' . $location['url'];
        }

        $this->saveUrls($path, $chunkId, $urls);
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
            $element->addChild('loc', $url);
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
            $this->getDirectoryRecursiveFiles($this->tmpPath),
            ['/' . $fileName . '.xml']
        );
        $this->saveUrls($this->tmpPath, $fileName, $filesUrls);
        $indexesUrls = $this->getFilesUrls(
            $this->getDirectoryFiles($this->tmpPath),
            ['/' . $fileName . '.xml']
        );
        if (count($indexesUrls) > 1) {
            $this->saveUrls($this->tmpPath, $fileName, $indexesUrls, false);
        }

        $siteMapIndexUrl = $this->domainUrl . '/' . $this->getTmpDirectoryName() . '/' . $fileName . '.xml';

        return $siteMapIndexUrl;
    }

    private function getFilesUrls(array $paths, array $excludes = []): array
    {
        $filesUrls = [];
        foreach ($paths as $path) {
            $parts = explode($this->tmpPath, $path);
            if (!in_array($parts[1], $excludes)) {
                $filesUrls[] = $this->domainUrl . '/' . $this->getRealDirectoryName() . $parts[1];
//                $filesUrls[] = $this->domainUrl . '/' . $this->getTmpDirectoryName() . $parts[1];
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

    private function removeTmpDirectory(): void
    {
        system("rm -rf " . escapeshellarg($this->tmpPath));
        $this->log('Removed ' . $this->tmpPath);
    }

    private function getRealDirectoryName(): string
    {
        $directoryParts = explode('/', $this->path);

        return $directoryParts[count($directoryParts) - 1];
    }

    private function getTmpDirectoryName(): string
    {
        $tmpDirectoryParts = explode('/', $this->tmpPath);

        return $tmpDirectoryParts[count($tmpDirectoryParts) - 1];
    }

    private function renameDirectories()
    {
        system("rm -rf " . escapeshellarg($this->path));
        system("mv " . escapeshellarg($this->tmpPath) . ' ' . escapeshellarg($this->path));
        $this->log('Renamed ' . $this->tmpPath . ' to ' . $this->path);
    }
}