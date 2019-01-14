<?php

namespace app\api\controller;

use app\api\model\OrderProduct;
use app\api\validate\OrderPlace;
use app\api\service\Token as TokenService;

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

        $orderService = new OrderProduct();

        $status = $orderService->replace($uid, $submitOrder);

        return $status;
    }
}
