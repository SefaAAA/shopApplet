<?php

namespace app\api\controller;

use app\api\validate\IDMustBePositiveInt;
use app\api\service\Pay as payService;

class Pay extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
    ];

    /**
     * 生成预支付订单信息给前端小程序
     * @param $orderID
     * @throws \app\lib\exception\SefaException
     */
    public function getPrepayOrder($orderID)
    {
        (new IDMustBePositiveInt())->goCheck();

        $payService = new payService($orderID);

        $payService->pay();
    }
}
