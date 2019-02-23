<?php

require_once("BaseDto.php");

class AddressDto {
    use BaseDto;

    private $pkey;
    private $firstOffset;
    private $lastOffset;

    private $code;

    private $oldpcode;

    private $pcode;

    private $prefkana;

    private $citykana;

    private $townKana;

    private $pref;

    private $city;

    private $town;

    private $col9;

    private $col10;

    private $col11;

    private $col12;

    private $col13;

    private $col14;

    public function __construct($data) {
        $this->code = $data[0];
        $this->oldpcode = $data[1];
        $this->pcode = $data[2];
        $this->prefkana = $data[3];
        $this->citykana = $data[4];
        $this->townKana = $data[5];
        $this->pref = $data[6];
        $this->city = $data[7];
        $this->town = $data[8];
        $this->col9 = $data[9];
        $this->col10 = $data[10];
        $this->col11 = $data[11];
        $this->col12 = $data[12];
        $this->col13 = $data[13];
        $this->col14 = $data[14];
    }

    public function isMultiRowTown(AddressDto $address) {
        $arr1 = get_object_vars($this);
        $arr2 = get_object_vars($address);

        foreach (["pkey", "firstOffset", "lastOffset", "town"] as $key) {
            unset($arr1[$key]);
            unset($arr2[$key]);
        }

        $result = $arr1 === $arr2;

        return $result;
    }

    public function isMultiRowAll(AddressDto $address) {
        $arr1 = get_object_vars($this);
        $arr2 = get_object_vars($address);

        foreach (["pkey", "firstOffset", "lastOffset", "town", "townKana"] as $key) {
            unset($arr1[$key]);
            unset($arr2[$key]);
        }

        $result = $arr1 === $arr2;

        return $result;
    }

    public function getCsvLine() {
        $arr1 = get_object_vars($this);
        foreach (["pkey", "firstOffset", "lastOffset"] as $key) {
            unset($arr1[$key]);
        }
        $result = "";
        foreach ($arr1 as $key => $col) {
            if ($result !== "") {
                $result .= ",";
            }
            if (preg_match("/^\d$/", $col) || in_array($key, ["code"])) {
                $result .= $col;
            } else {
                $result .= "\"{$col}\"";
            }
        }
        return $result;
    }

    public function toArray() {
        $arr1 = get_object_vars($this);
        foreach (["pkey", "firstOffset", "lastOffset"] as $key) {
            unset($arr1[$key]);
        }
        return $arr1;
    }
}
