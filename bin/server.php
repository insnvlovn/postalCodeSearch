<?php

require_once("SearchQueryDto.php");
require_once("SearchService.php");

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
spl_autoload_register(function($class) {
    trigger_error("$class not loaded.", E_USER_ERROR);
});

ini_set("memory_limit", "1024M");

call_user_func(function() {
    $pid = getmypid();
    $fp = fopen("run/postalCodeSearch.pid", "x");
    fwrite($fp, $pid);
    fclose($fp);
});

$csvFile = "file/KEN_ALL.UTF8.fixed.CSV";

$fileInfo = include("cache/fileInfo.php");
$searchInfo = include("cache/searchInfo.php");

function _http_default($req) {
    global $csvFile, $fileInfo, $searchInfo;

    $req->addHeader("Access-Control-Allow-Origin", "*", EventHttpRequest::OUTPUT_HEADER);
    $req->addHeader("Content-Type", "application/json", EventHttpRequest::OUTPUT_HEADER);
    try {
        parse_str(parse_url($req->getUri(), PHP_URL_QUERY), $query);

        $searchQuery = new SearchQueryDto($query);

        //echo "Output headers:"; var_dump($req->getOutputHeaders(), $query, $searchQuery);

        $scoreList = SearchService::search($searchQuery->getSearchWordList(), $searchInfo);

        //var_dump($scoreList);

        $cntAll = count($scoreList);

        $cnt = 0;
        foreach ($scoreList as $key => $val) {
            $cnt++;
            if ($cnt > $searchQuery->getLimit()) {
                unset($scoreList[$key]);
            }
        }

        $result = SearchService::getResult($scoreList, $fileInfo, $csvFile);

        $json = [
            "count" => $cntAll,
            "data" => $result,
        ];
        $jsonString = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        //echo "Output headers:"; var_dump($json);

        $buf = new EventBuffer();
        $buf->add($jsonString);

        $req->sendReply(200, "OK", $buf);
    } catch (Exception $e) {
        $buf = new EventBuffer();
        $buf->add(json_encode([
            "message" => $e->getMessage(),
            "severity" => $e->getSeverity(),
            "filename" => $e->getFile(),
            "lineno" => $e->getLine(),
            "trace" => (string)$e,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $req->sendReply(500, "Internal Server Error", $buf);
    }
}

$port = 8010;

$base = new EventBase();
$http = new EventHttp($base);

$http->setAllowedMethods(EventHttpRequest::CMD_GET);

$http->setDefaultCallback("_http_default");

$http->bind("0.0.0.0", 8010);
$base->loop();
