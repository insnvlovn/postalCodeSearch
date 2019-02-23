<?php

require_once("AddressDto.php");
require_once("SearchService.php");

ini_set("memory_limit", "1024M");

$csvFile = "file/KEN_ALL.UTF8.fixed.CSV";

$fileInfo = [];
$searchInfo = [];

$firstOffset = 0;
$lastOffset = 0;
$prevAddress = null;

$fp = fopen($csvFile, "r");
$pkey = 0;

while (($data = fgetcsv($fp, 10000, ",")) !== false) {
    $lastOffset = ftell($fp);

    $address = new AddressDto($data);
    $address->setPkey($pkey);
    $address->setFirstOffset($firstOffset);
    $address->setLastOffset($lastOffset);

    $wordList = [];
    foreach ($data as $key => $val) {
        $wordList = array_merge($wordList, SearchService::parseWord($val));
    }
    $wordList = array_unique($wordList);
    foreach ($wordList as $word) {
        $cacheKey = SearchService::encodeCacheKey($word);
        $searchInfo[$cacheKey][$pkey] = true;
    }
    //var_dump($data, $wordList);

    $fileInfo[$pkey] = [
        "firstOffset" => $firstOffset,
        "lastOffset" => $lastOffset,
    ];

    $prevAddress = $address;
    $firstOffset = $lastOffset;
    $pkey++;
}

fclose($fp);

call_user_func(function() use(&$searchInfo) {
    ksort($searchInfo);
    foreach ($searchInfo as $key => $val) {
        $searchInfo[$key] = array_keys($val);
        sort($searchInfo[$key]);
    }
    $fp = fopen("cache/searchInfo.php", "w");
    fwrite($fp, "<"."?php\nreturn ".var_export($searchInfo, true).";\n");
    fclose($fp);
});

call_user_func(function() use(&$fileInfo) {
    $fp = fopen("cache/fileInfo.php", "w");
    fwrite($fp, "<"."?php\nreturn ".var_export($fileInfo, true).";\n");
    fclose($fp);
});
