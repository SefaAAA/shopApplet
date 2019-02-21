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
        'products' => 'require|checkProducts'
    ];

    protected $message = [
        'products' => '提交的订单参数不合法'
    ];

    private $singleRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger'
    ];

    private $singleMessage = [
        'product_id' => '商品订单编号必须是正整数',
        'count' => '商品购买数量必须是正整数'
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
        return true;
    }
    //单独验证提交的订单信息中每个商品的参数信息
    private function checkProduct($value)
    {
        $validate = new BaseValidate($this->singleRule, $this->singleMessage);

        return $validate->check($value);
    }
}