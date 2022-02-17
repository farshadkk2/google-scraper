<?php

use App\GoogleSearch;
use App\Language;

require_once __DIR__ . '/vendor/autoload.php';

$results = [];

$google = new GoogleSearch();
$persian = new Language("fa");
$google->setLanguage($persian);
$google->setDevice("mobile");
$google->setQuery("نی نی پلاس");

try {
    $results = $google->next();
} catch (Exception $e) {
    if ($e->getCode() == 1) echo "Failed to connect to Google!";
    elseif ($e->getCode() == 2) echo "We are blocked by Google temporarily! :-(";
    else echo "Unknown error: {$e->getMessage()}";
}

var_dump($results);

