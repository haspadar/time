<?php
$url = $_SERVER['REQUEST_URI'] ? mb_substr($_SERVER['REQUEST_URI'], '1') : '';
$hasSlash = str_contains($url, '/');
if ($_SERVER['REQUEST_URI'] == '/compare') :
    $layout = 'compare_start.php';
elseif ($hasSlash) :
    $layout = 'compare_result.php';
else :
    $layout = 'location.php';
endif;

require_once '../layouts/' . $layout;
