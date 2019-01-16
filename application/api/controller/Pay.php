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
     * @param int $id 预支付订单 ID
     * @throws \app\lib\exception\SefaException
     */
    public function getPrepayOrder($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $payService = new payService($id);

        $payService->pay();
    }
}
