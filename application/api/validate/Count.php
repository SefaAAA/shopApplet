<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/9
 * Time: 13:43
 */

namespace app\api\validate;


class Count extends BaseValidate
{
    protected $rule = [
        'count' => 'isPositiveInteger|between:0, 15',
    ];

    protected $message = [
        'count' => 'count 必须是 1 到 15 之间的整数'
    ];
}