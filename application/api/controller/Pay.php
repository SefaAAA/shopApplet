<?php

namespace app\api\controller;

use app\api\service\WxPayNotify;
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

    /**
     * 微信支付成功异步通知处理，微信通知时会过滤掉后面的所有查询参数，如果想要进行断点调试，可以另外设置一个方法进行转发携带 Xdebug 参数
     */
    public function receiveNotify()
    {
        $notify = new WxPayNotify();    //实例化异步通知处理类

        $config = new \WxPayConfig();

        $notify->Handle($config, false);
    }
}
