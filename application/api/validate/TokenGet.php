<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/9
 * Time: 21:07
 */

namespace app\api\validate;


class TokenGet extends BaseValidate
{
    protected $rule = [
        'code' => 'require|notEmpty'
    ];

    protected $message = [
        'code' => '微信 code 不能为空'
    ];
}