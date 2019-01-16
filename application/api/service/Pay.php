<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/15
 * Time: 16:17
 */

namespace app\api\service;


use app\api\model\OrderInfo;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\SefaException;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php'); // 导入微信支付 SDK

class Pay
{
    private $orderID;
    private $orderSn;

    public function __construct($orderID)
    {
        $this->orderID = $orderID;
    }

    public function pay()
    {
        $orderService = new OrderService();

        $this->checkOrderValid();

        //检测库存量
        $status = $orderService->checkOrderStock($this->orderID);
        if (!$status['pass']) {
            throw new SefaException([
                'message' => '订单中的商品库存量不足',
                'errorCode' => 6000
            ]);
        }

        $this->prePay($status['order_amount']);
    }

    private function prePay($totalFee)
    {
        $openid = TokenService::getCurrentUserTokenVar('openid');

        $wxOrderData = new \WxPayUnifiedOrder();    //实例化统一下单输入类
        $wxOrderData->SetOut_trade_no($this->orderSn);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalFee * 100);
        $wxOrderData->SetBody('XXX');   // 商品名称
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url('');  //微信支付异步通知地址

        $prePayResult = \WxPayApi::unifiedOrder($wxOrderData);   //请求统一下单接口

        if ($prePayResult['return_code'] != 'SUCCESS' || $prePayResult['result_code'] != 'SUCCESS') {
            Log::record('订单预支付失败：失败原因：'.json_encode($prePayResult, 320), 'error');
        }
    }

    public function checkOrderValid()
    {
        //监测该订单是否存在
        $order = OrderInfo::where('id', '=', $this->orderID)->find();

        if (is_null($order)) {
            throw new SefaException([
                'code' => 404,
                'message' => '订单不存在',
                'errorCode' => 6001
            ]);
        }
        //检测该订单是否归当前用户所有
        if (!Token::isValidOperate($order->user_id)) {
            throw new SefaException([
                'code' => 403,
                'message' => '订单不是本人订单，禁止操作',
                'errorCode' => 6002
            ]);
        }
        //检测订单是否已经支付
        if ($order->status != OrderStatusEnum::UNPAID) {
            throw new SefaException([
                'message' => '订单已经被支付',
                'errorCode' => 6003
            ]);
        }

        $this->orderSn = $order->order_sn;
    }
}