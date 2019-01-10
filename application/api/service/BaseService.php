<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/10
 * Time: 15:17
 */

namespace app\api\service;


class BaseService
{
    /**
     * 常用的生成 token 的方法封装
     * @return string
     */
    public static function generateToken()
    {
        $randChars = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $salt = config('secure.token_salt');

        return md5($randChars.$timestamp.$salt);
    }
}