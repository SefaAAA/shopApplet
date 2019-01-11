<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/11
 * Time: 9:44
 */

namespace app\api\validate;


class AddressInfo extends BaseValidate
{
    protected $rule = [
        'consignee' => 'require|notEmpty',
        'mobile' => 'require|notEmpty',
        'country' => 'require|notEmpty',
        'province' => 'require|notEmpty',
        'city' => 'require|notEmpty',
        'detail' => 'require|notEmpty',
    ];
}