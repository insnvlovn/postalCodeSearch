<?php

require_once("AddressDto.php");
require_once("SearchService.php");

ini_set("memory_limit", "1024M");

$csvFile = "file/KEN_ALL.UTF8.fixed.CSV";

$fileInfo = include("cache/fileInfo.php");
$searchInfo = include("cache/searchInfo.php");

$searchWordList = $argv;
array_shift($searchWordList);

if (count($searchWordList) == 0) {
    print "Usage: " . basename(__FILE__) . " searchword...\n";
    exit;
}

$scoreList = SearchService::search($searchWordList, $searchInfo);

SearchService::dumpScore($scoreList, $fileInfo, $csvFile, $searchWordList);
