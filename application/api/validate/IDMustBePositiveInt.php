<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/5
 * Time: 23:02
 */

namespace app\api\validate;


class IDMustBePositiveInt extends BaseValidate
{
    protected $rule = [
        'id' => 'require|isPositiveInteger',
    ];

    protected $message = [
        'id' => 'ID 必须是一个正整数'
    ];
}