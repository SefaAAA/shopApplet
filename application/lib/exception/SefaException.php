<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/6
 * Time: 14:25
 * 自定义异常类继承于框架异常基类，作为 RESTFul API 异常类，区别于服务器内部异常
 */

namespace app\lib\exception;


use think\Exception;

class SefaException extends Exception
{
    public $code = 400;
    public $msg = '错误的请求';
    public $errorCode = 1000;

    public function __construct($params = [])
    {
        if (empty($params) || !is_array($params)) {
            return;
        }

        if (array_key_exists('code', $params)) {
            $this->code = $params['code'];
        }

        if (array_key_exists('message', $params)) {
            $this->msg = $params['message'];
        }

        if (array_key_exists('errorCode', $params)) {
            $this->errorCode = $params['errorCode'];
        }
    }
}