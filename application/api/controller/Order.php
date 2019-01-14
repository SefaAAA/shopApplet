<?php

namespace app\api\controller;

use app\api\validate\OrderPlace;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
    ];

    public function placeOrder()
    {
        (new OrderPlace())->goCheck();

        $submitOrder = input('post.products/a');    //修饰器 a 表示参数是数组

        $uid = TokenService::getCurrentUserTokenVar('uid');

        $order = new OrderService();

        return $order->place($uid, $submitOrder);
    }
}











