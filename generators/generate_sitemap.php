#!/usr/bin/php
<?php

use WhatTime\Sitemap;

require_once '../config.php';

$sitemap = new Sitemap('https://www.what-time.info', dirname(__DIR__) . '/public/sitemaps');
$sitemap->generate();