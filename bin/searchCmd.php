<?php

require_once("AddressDto.php");
require_once("SearchService.php");

ini_set("memory_limit", "1024M");

$csvFile = "file/KEN_ALL.UTF8.fixed.CSV";

$fileInfo = include("cache/fileInfo.php");
$searchInfo = include("cache/searchInfo.php");

$searchWordList = $argv;
array_shift($searchWordList);

// var_dump($searchWordList);
// exit;

if (count($searchWordList) == 0) {
    print "Usage: " . basename(__FILE__) . " searchword...\n";
    exit;
}

$scoreList = SearchService::search($searchWordList, $searchInfo);

// foreach ($searchWordList as $key => $word) {
//     $wordList = SearchService::parseWord($word);
//     if (count($wordList) == 0) {
//         break;
//     }
//     if ($key == 0) {
//         foreach ($wordList as $word) {
//             $cacheKey = SearchService::encodeCacheKey($word);
//             if (!isset($searchInfo[$cacheKey])) {
//                 continue;
//             }
//             $fileIndexList = $searchInfo[$cacheKey];
//             foreach ($fileIndexList as $pkey) {
//                 @$scoreList[$pkey]++;
//             }
//         }
//     } else {
//         // 検索ワード2つ目以降はAND演算
//         $tmpScoreList = [];
//         foreach ($wordList as $word) {
//             $cacheKey = SearchService::encodeCacheKey($word);
//             if (!isset($searchInfo[$cacheKey])) {
//                 continue;
//             }
//             $fileIndexList = $searchInfo[$cacheKey];
//             foreach ($fileIndexList as $pkey) {
//                 if (isset($scoreList[$pkey])) {
//                     @$tmpScoreList[$pkey]++;
//                 }
//             }
//         }
//         foreach ($tmpScoreList as $pkey => $score) {
//             if (isset($scoreList[$pkey])) {
//                 $tmpScoreList[$pkey] += $scoreList[$pkey];
//             }
//         }
//         $scoreList = $tmpScoreList;
//     }
// }

// //$ngramList = array_unique($ngramList);

// var_dump($ngramList);


// foreach ($searchInfo as $cacheWord => $fileIndexList) {
//     if (in_array(SearchService::decodeCacheKey($cacheWord), $ngramList, true)) {
//         foreach ($fileIndexList as $pkey) {
//             @$scoreList[$pkey]++;
//         }
//     }
// }

// arsort($scoreList);

// print "\$searchWordList: " . json_encode($searchWordList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
// print "count(\$scoreList): " . count($scoreList) . "\n";
// $fp = fopen($csvFile, "r");
// foreach ($scoreList as $pkey => $score) {
//     print "### \$pkey : $pkey\n";
//     print "### \$score: $score\n";
//     fseek($fp, $fileInfo[$pkey]["firstOffset"]);
//     print fread($fp, $fileInfo[$pkey]["lastOffset"] - $fileInfo[$pkey]["firstOffset"]);
// }
// fclose($fp);

SearchService::dumpScore($scoreList, $fileInfo, $csvFile, $searchWordList);

// print SearchService::getJson($scoreList, $fileInfo, $csvFile);
// print "\n";

// $sortList = [];
// $fp = fopen($csvFile, "r");
// foreach ($scoreList as $pkey => $score) {
//     fseek($fp, $fileInfo[$pkey]["firstOffset"]);
//     $csv = fread($fp, $fileInfo[$pkey]["lastOffset"] - $fileInfo[$pkey]["firstOffset"]);
//     $sortList[] = [
//         "csv" => $csv,
//         "len" => strlen($csv),
//         "pkey" => $pkey,
//         "score" => $score,
//     ];
// }
// fclose($fp);
