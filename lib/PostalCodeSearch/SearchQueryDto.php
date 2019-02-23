<?php

require_once("BaseDto.php");

class SearchQueryDto {
    use BaseDto;

    private $limit = 10;

    private $searchWordList = [];

    public function __construct($query) {
        if (isset($query["limit"])) {
            $limit = (int)$query["limit"];
            $limit = max(10, $limit);
            $limit = min(1000, $limit);
            $this->limit = $limit;
        }
        if (isset($query["s"]) && is_array($query["s"])) {
            $searchWordList = $query["s"];
            array_map(function($str){
                return (string)$str;
            }, $searchWordList);
            $this->searchWordList = $searchWordList;
        }
    }
}
