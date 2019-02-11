<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/17
 * Time: 15:36
 */

namespace app\api\validate;


class PagingParameter extends BaseValidate
{
    protected $rule = [
        'page' => 'isPositiveInteger',
        'size' => 'isPositiveInteger'
    ];

    protected $message = [
        'page' => '页数参数必须是正整数',
        'size' => '每页记录数必须是正整数'
    ];
}