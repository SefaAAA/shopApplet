<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function curl($url, $type = 'POST', $data='', $https = false, $referer = 'http://www.baidu.com')
{
    $ch = curl_init();
    curl_setopt(CURLOPT_URL, $url);
    curl_setopt(CURLOPT_RETURNTRANSFER, true);

    if (strtolower($type) == 'post') {
        curl_setopt(CURLOPT_POST, true);
        curl_setopt(CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
    }

    if ($https) {
        curl_setopt(CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt(CURLOPT_SSL_VERIFYHOST, false);
    }

    curl_setopt(CURLOPT_REFERER, $referer);
    curl_setopt(CURLOPT_CONNECTTIMEOUT, 5);

    $res = curl_exec($ch);
    curl_close($ch);

    return $res;
}