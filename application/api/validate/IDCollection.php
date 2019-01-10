<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/8
 * Time: 12:30
 */

namespace app\api\validate;


class IDCollection extends BaseValidate
{
    protected $rule = [
        'ids' => "require|checkIDs"
    ];

    protected $message = [
        'ids' => "ids 参数必须是多个 Theme ID 由英文逗号隔开的字符串"
    ];
}