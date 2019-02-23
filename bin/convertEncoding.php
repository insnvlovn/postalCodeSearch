<?php

$buf = file_get_contents("file/KEN_ALL.CSV");

$buf = mb_convert_encoding($buf, "UTF-8", "SJIS-win");

$buf = preg_replace("/\r\n|[\r\n]/", "\n", $buf);

$fp = fopen("file/KEN_ALL.UTF8.CSV", "w");
fwrite($fp, $buf);
fclose($fp);
