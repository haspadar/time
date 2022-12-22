#!/usr/bin/php
<?php

use WhatTime\Sitemap;

require_once '../config.php';

$sitemap = new Sitemap\Db();
$sitemap->generate();