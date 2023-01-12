#!/usr/bin/php
<?php

use WhatTime\Sitemap;

require_once '../config.php';

$sitemapDb = new Sitemap\Db();
$sitemapDb->generate();