<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/12
 * Time: 11:57
 */

namespace app\lib\exception;


class ForbiddenException extends SefaException
{
    public $code = 403;
    public $message = '权限不足，禁止访问';
    public $errorCode = 1004;
}