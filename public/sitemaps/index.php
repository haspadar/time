<?php

use WhatTime\Sitemap;

require_once '../../config.php';

$sitemapXml = (new Sitemap\Xml('https://www.what-time.info', 40000));
if ($queryFileId = intval($_GET['file'] ?? 0)) {
    $response = $sitemapXml->generatePageFile($queryFileId);
} else {
    $response = $sitemapXml->generateIndexFile();
}

if ($response) {
    header('Content-Type: text/xml');
    echo $response;
} else {
    header("HTTP/1.0 404 Not Found");
}