<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/13
 * Time: 12:52
 */

namespace app\api\validate;


class OrderPlace extends BaseValidate
{
    protected $rule = [
        'products' => 'checkProducts'
    ];

    private $singleRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger'

    ];

    /**
     * 这里的方法验证的是一个数组类型的参数，需要循环遍历子元素进行深层验证，该项目中并没有商家的概念，如果是多商户商城，收到的订单参数还按照商户进行分组，从而可能是三维数据
     * @param $values
     * @return bool
     */
    protected function checkProducts($values)
    {
        if (!is_array($values) || empty($values)) {
            return false;
        }
        //循环遍历每个商品信息进行验证
        foreach ($values as $value) {
            if (!$this->checkProduct($value)) {
                return false;
            }
        }
    }
    //单独验证提交的订单信息中每个商品的参数信息
    private function checkProduct($value)
    {
        $validate = new BaseValidate($this->singleRule);

        return $validate->check($value);
    }
}