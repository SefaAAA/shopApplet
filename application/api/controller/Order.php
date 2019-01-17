<?php

namespace app\api\controller;

use app\api\model\OrderInfo;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\lib\exception\SefaException;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getOrderByUser,getDetail']
    ];

    public function placeOrder()
    {
        (new OrderPlace())->goCheck();

        $submitOrder = input('post.products/a');    //修饰器 a 表示参数是数组

        $uid = TokenService::getCurrentUserTokenVar('uid');

        $order = new OrderService();

        return $order->place($uid, $submitOrder);
    }

    public function getOrderByUser($page = 1, $size = 15)
    {
        (new PagingParameter())->goCheck();

        $uid = TokenService::getCurrentUID();

        $orders = OrderInfo::getSummaryByUser($uid)->hidden(['prepay_id', 'country', 'province', 'city', 'district', 'detail']);

        return $orders->isEmpty() ? [] : $orders->toArray();
    }

    public function getDetail($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $orderDetail = OrderInfo::get($id);

        if (is_null($orderDetail)) {
            throw new SefaException([
                'code' => 404,
                'message' => '订单信息不存在',
                'errorCode' => 5002
            ]);
        }

        return $orderDetail->hidden(['prepay_id']);
    }
}