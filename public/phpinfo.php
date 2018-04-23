<?php
    $host = "http://jisuznwd.market.alicloudapi.com";
    $path = "/iqa/query";
    $method = "GET";
    $appcode = "09e1da306ca64c22831b2030a10866c4";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "question=%E6%9D%AD%E5%B7%9E%E5%A4%A9%E6%B0%94";
    $bodys = "";
    $url = $host . $path . "?" . $querys;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    echo '<pre>';
    $res = json_decode(curl_exec($curl));
    var_dump($res,true);
    echo '<hr>';
    var_dump($res->result->content);
//    echo curl_exec($curl);
?>