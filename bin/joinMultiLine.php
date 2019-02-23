<?php

require_once("AddressDto.php");
require_once("SearchService.php");

define("MULTI_LINE_NONE", 0);
define("MULTI_LINE_TOWN", 1);
define("MULTI_LINE_KANA", 2);
define("MULTI_LINE_ALL", 3);

$csvFile = "file/KEN_ALL.UTF8.CSV";

$firstOffset = 0;
$lastOffset = 0;
$prevAddress = null;

$fp = fopen($csvFile, "r");
$pkey = 0;

$multiLineMode = MULTI_LINE_NONE;

while (($data = fgetcsv($fp, 10000, ",")) !== false) {
    $lastOffset = ftell($fp);

    $address = new AddressDto($data);
    $address->setPkey($pkey);
    $address->setFirstOffset($firstOffset);
    $address->setLastOffset($lastOffset);

    $townStartFlag = strpos($address->getTown(), "（") !== false;
    $kanaStartFlag = strpos($address->getTownKana(), "(") !== false;
    $townEndFlag = strpos($address->getTown(), "）") !== false;
    $kanaEndFlag = strpos($address->getTownKana(), ")") !== false;
    switch ($multiLineMode) {
        case MULTI_LINE_NONE:
            if ($townStartFlag && $townEndFlag && $kanaStartFlag && $kanaEndFlag) {
                // resume
            } elseif ($townStartFlag && $townEndFlag) {
                // resume
            } elseif ($kanaStartFlag && $kanaEndFlag) {
                trigger_error("analyze error.", E_USER_ERROR);
            } elseif ($townStartFlag && $kanaStartFlag) {
                $multiLineMode = MULTI_LINE_ALL;
            } elseif ($townStartFlag) {
                $multiLineMode = MULTI_LINE_TOWN;
            } elseif ($kanaStartFlag) {
                trigger_error("analyze error.", E_USER_ERROR);
            } else {
                // resume
            }
            break;
        case MULTI_LINE_TOWN:
            if ($address->isMultiRowTown($prevAddress)) {
                $address->setTown($prevAddress->getTown() . $address->getTown());
            } else {
                trigger_error("analyze error.", E_USER_ERROR);
            }
            if ($townEndFlag) {
                $multiLineMode = MULTI_LINE_NONE;
            } elseif ($townEndFlag || $kanaEndFlag) {
                trigger_error("analyze error.", E_USER_ERROR);
            }
            break;
        case MULTI_LINE_ALL:
            if ($address->isMultiRowAll($prevAddress)) {
                $address->setTown($prevAddress->getTown() . $address->getTown());
                $address->setTownKana($prevAddress->getTownKana() . $address->getTownKana());
            } else {
                trigger_error("analyze error.", E_USER_ERROR);
            }
            if ($townEndFlag && $kanaEndFlag) {
                $multiLineMode = MULTI_LINE_NONE;
            } elseif ($townEndFlag || $kanaEndFlag) {
                trigger_error("analyze error.", E_USER_ERROR);
            }
            break;
    }

    if ($multiLineMode === MULTI_LINE_NONE) {
        print $address->getCsvLine()."\n";
    }

    $prevAddress = $address;
    $firstOffset = $lastOffset;
    $pkey++;
}

fclose($fp);
