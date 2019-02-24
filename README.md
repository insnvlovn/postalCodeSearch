# 住所をN-Gram(N=2)で検索

## 参考URL

https://www.post.japanpost.jp/zipcode/dl/kogaki-zip.html


## 初期設定

    curl -o file/ken_all.zip https://www.post.japanpost.jp/zipcode/dl/kogaki/zip/ken_all.zip
    unzip -d file/ file/ken_all.zip
    alias php='php -c conf/php.ini'
    php bin/convertEncoding.php
    php bin/joinMultiLine.php > file/KEN_ALL.UTF8.fixed.CSV
    php bin/createIndex.php

## 検索コマンド

grepと比べ遅いが完全マッチでなくともヒットする。

    $ time php bin/searchCmd.php 東京都台東区駒形バンダイの玩具第3部の星|head -n17
    ## $searchWordList: ["東京都台東区駒形バンダイの玩具第3部の星"]
    ## count($scoreList): 17634
    ### $pkey : 39254
    ### $score: 5
    13106,"111  ","1110043","ﾄｳｷｮｳﾄ","ﾀｲﾄｳｸ","ｺﾏｶﾞﾀ","東京都","台東区","駒形",0,0,1,0,0,0
    ### $pkey : 39238
    ### $score: 4
    13106,"110  ","1100000","ﾄｳｷｮｳﾄ","ﾀｲﾄｳｸ","ｲｶﾆｹｲｻｲｶﾞﾅｲﾊﾞｱｲ","東京都","台東区","以下に掲載がない場合",0,0,0,0,0,0
    ### $pkey : 74768
    ### $score: 4
    24208,"51804","5180405","ﾐｴｹﾝ","ﾅﾊﾞﾘｼ","ｽｽﾞﾗﾝﾀﾞｲﾋｶﾞｼ5ﾊﾞﾝﾁｮｳ","三重県","名張市","すずらん台東５番町",0,0,0,0,0,0
    ### $pkey : 39261
    ### $score: 4
    13106,"110  ","1100003","ﾄｳｷｮｳﾄ","ﾀｲﾄｳｸ","ﾈｷﾞｼ","東京都","台東区","根岸",0,0,1,0,0,0
    ### $pkey : 39262
    ### $score: 4
    13106,"111  ","1110023","ﾄｳｷｮｳﾄ","ﾀｲﾄｳｸ","ﾊｼﾊﾞ","東京都","台東区","橋場",0,0,1,0,0,0
    
    real	0m1.855s
    user	0m1.503s
    sys	0m0.352s

半角スペースで区切って絞り込みできる。

    $ time php bin/searchCmd.php アイウエオ カキクケコ タチツテト
    ## $searchWordList: ["アイウエオ","カキクケコ","タチツテト"]
    ## count($scoreList): 1
    ### $pkey : 67944
    ### $score: 3
    23202,"444  ","4440065","ｱｲﾁｹﾝ","ｵｶｻﾞｷｼ","ｶｷﾀﾁｮｳ","愛知県","岡崎市","柿田町",0,0,0,0,0,0
    
    real	0m1.789s
    user	0m1.482s
    sys	0m0.303s

## APIサーバ

遅い理由は起動の際にとても大きいファイルを読み込むことが主な原因と思われるので、
起動しっぱなしだと、早いかもしれない。

http://php.net/manual/ja/event.examples.php

> 例8 Simple HTTP server

を参照のこと。

    sh bin/postalCodeSearch.sh restart

計測し早くなった。

    $ time curl "http://localhost:8010/?$(php -r 'foreach(["アイウエオ","カキクケコ","タチツテト"]as$s){print "s".urlencode("[]")."=".urlencode($s)."&";}')"|jq
      % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                     Dload  Upload   Total   Spent    Left  Speed
    100   276  100   276    0     0   134k      0 --:--:-- --:--:-- --:--:--  134k
    [
      {
        "code": "23202",
        "oldpcode": "444  ",
        "pcode": "4440065",
        "prefkana": "ｱｲﾁｹﾝ",
        "citykana": "ｵｶｻﾞｷｼ",
        "townKana": "ｶｷﾀﾁｮｳ",
        "pref": "愛知県",
        "city": "岡崎市",
        "town": "柿田町",
        "col9": "0",
        "col10": "0",
        "col11": "0",
        "col12": "0",
        "col13": "0",
        "col14": "0"
      }
    ]
    
    real	0m0.023s
    user	0m0.012s
    sys	0m0.011s


## webサービス

html+javascriptで検索文字列を渡し受け取って描画。

    htdocs/index.html
    htdocs/js/postalCodeSearch.js
    htdocs/css/postalCodeSearch.css

apiにはlimitもある。デフォルト値は10。サンプルにはlimitを渡す機能は無い。



## 実行環境

    gentoo linux
    
    $ uname -a
    Linux vmware-gentoo1 4.14.52-gentoo #1 SMP Sat Jul 14 18:03:25 JST 2018 x86_64 Intel(R) Core(TM) i5-3470 CPU @ 3.20GHz GenuineIntel GNU/Linux
    
    $ php -v
    PHP 7.3.2 (cli) (built: Feb 18 2019 00:26:00) ( NTS )
    Copyright (c) 1997-2018 The PHP Group
    Zend Engine v3.3.2, Copyright (c) 1998-2018 Zend Technologies
        with Zend OPcache v7.3.2, Copyright (c) 1999-2018, by Zend Technologies
    
    dev-php/pecl-event-2.4.3

postalCodeSearch.shを実行する場合、PECLのeventモジュールが必要です。

http://php.net/manual/ja/event.installation.php



## LICENSE

このソフトウェアはMITライセンスの下でリリースされています。LICENSE.txtをご覧ください。

This software is released under the MIT License, see LICENSE.txt.
