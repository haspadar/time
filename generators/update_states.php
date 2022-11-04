<?php
require_once '../config.php';

$usStates = DB::query('SELECT ac.* FROM admin1_codes AS ac WHERE code IN (SELECT CONCAT(c.country_code, ".", c.admin1_code) FROM cities AS c WHERE country_code="US")');
