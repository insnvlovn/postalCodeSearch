<?php

require_once("AddressDto.php");

class SearchService {

    public static function decodeCacheKey($word) {
        return substr($word, 1);
    }
    public static function encodeCacheKey($word) {
        return "@{$word}";
    }

    public static function filterWord($word) {
        $result = mb_convert_kana($word, "asKVC");
        return $result;
    }

    public static function parseWord(String $word) {
        $word = self::filterWord($word);

        if (!preg_match_all("/\S{1}(?=\S{1})/u", $word, $matches, PREG_OFFSET_CAPTURE)) {
            return [];
        }

        $result = [];

        foreach ($matches[0] as $k => $v) {
            $val = $v[0];
            $offset = $v[1];
            $suffix = mb_substr(substr($word, $offset), 1, 1);
            $result[] = $val . $suffix;
        }

        $result = array_unique($result);

        return $result;
    }

    public static function search($searchWordList, $searchInfo) {
        $scoreList = [];

        foreach ($searchWordList as $key => $word) {
            $wordList = SearchService::parseWord($word);
            if (count($wordList) == 0) {
                break;
            }
            if ($key == 0) {
                foreach ($wordList as $word) {
                    $cacheKey = SearchService::encodeCacheKey($word);
                    if (!isset($searchInfo[$cacheKey])) {
                        continue;
                    }
                    $fileIndexList = $searchInfo[$cacheKey];
                    foreach ($fileIndexList as $pkey) {
                        if (!isset($scoreList[$pkey])) {
                            $scoreList[$pkey] = 0;
                        }
                        $scoreList[$pkey]++;
                    }
                }
            } else {
                // 検索ワード2つ目以降はAND演算
                $tmpScoreList = [];
                foreach ($wordList as $word) {
                    $cacheKey = SearchService::encodeCacheKey($word);
                    if (!isset($searchInfo[$cacheKey])) {
                        continue;
                    }
                    $fileIndexList = $searchInfo[$cacheKey];
                    foreach ($fileIndexList as $pkey) {
                        if (isset($scoreList[$pkey])) {
                            if (!isset($tmpScoreList[$pkey])) {
                                $tmpScoreList[$pkey] = 0;
                            }
                            $tmpScoreList[$pkey]++;
                        }
                    }
                }
                foreach ($tmpScoreList as $pkey => $score) {
                    if (isset($scoreList[$pkey])) {
                        $tmpScoreList[$pkey] += $scoreList[$pkey];
                    }
                }
                $scoreList = $tmpScoreList;
            }
        }

        arsort($scoreList);

        return $scoreList;
    }

    public static function dumpScore($scoreList, $fileInfo, $csvFile, $searchWordList) {
        print "## \$searchWordList: " . json_encode($searchWordList, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
        print "## count(\$scoreList): " . count($scoreList) . "\n";
        $fp = fopen($csvFile, "r");
        foreach ($scoreList as $pkey => $score) {
            print "### \$pkey : $pkey\n";
            print "### \$score: $score\n";
            fseek($fp, $fileInfo[$pkey]["firstOffset"]);
            print fread($fp, $fileInfo[$pkey]["lastOffset"] - $fileInfo[$pkey]["firstOffset"]);
        }
        fclose($fp);
    }

    public static function getResult($scoreList, $fileInfo, $csvFile) {
        $result = [];

        $fp = fopen($csvFile, "r");
        foreach ($scoreList as $pkey => $score) {
            $firstOffset = $fileInfo[$pkey]["firstOffset"];
            $lastOffset = $fileInfo[$pkey]["lastOffset"];
            fseek($fp, $fileInfo[$pkey]["firstOffset"]);
            $data = fgetcsv($fp, $lastOffset - $firstOffset);

            //var_dump(basename(__FILE__), __LINE__);
            $address = new AddressDto($data);
            //var_dump(basename(__FILE__), __LINE__);
            $address->setPkey($pkey);
            $address->setFirstOffset($firstOffset);
            $address->setLastOffset($lastOffset);

            $result[] = $address->toArray();
        }
        fclose($fp);

        return $result;
    }

    public static function getJson($scoreList, $fileInfo, $csvFile) {
        $result = self::getResult($scoreList, $fileInfo, $csvFile);
        return json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
