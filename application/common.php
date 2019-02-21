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
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if (strtolower($type) == 'post') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
    }

    if ($https) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    curl_setopt($ch, CURLOPT_REFERER, $referer);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    $res = curl_exec($ch);

    if ($res === false) {
        \think\Log::record('cURL 请求出错：出错原因：'.curl_error($ch), 'error');
    }

    curl_close($ch);
    return $res;
}

/**
 * @param int $length 所需字符串长度
 * @return string 随机字符串
 */
function getRandChar($length = 32)
{
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $maxKey = strlen($strPol) - 1;
    $str = '';

    for ($i = 0;$i < $length;$i++)
    {
        $str .= $strPol[rand(0, $maxKey)];
    }

    return $str;
}