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

        return $this->prePay($status['order_amount']);
    }

    private function prePay($totalFee)
    {
        $openid = TokenService::getCurrentUserTokenVar('openid');

        $wxOrderData = new \WxPayUnifiedOrder();    //实例化统一下单输入类
        $wxOrderData->SetAppid(config('wx.app_id'));
        $wxOrderData->SetOut_trade_no($this->orderSn);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalFee * 100);
        $wxOrderData->SetBody('XXX');   // 商品名称
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(url('pay/notify', '', '', true));  //微信支付异步通知地址

        $wxPayConfig = new \WxPayConfig();
        $prePayResult = \WxPayApi::unifiedOrder($wxPayConfig, $wxOrderData);   //请求统一下单接口

        $errorMsg = '';
        if (empty($prePayResult)) {
            $errorMsg = '请求出错';
        } else {
            if ($prePayResult['return_code'] != 'SUCCESS') {
                $errorMsg = $prePayResult['return_msg'];
            }
            if ($prePayResult['return_code'] == 'SUCCESS' && $prePayResult['result_code'] != 'SUCCESS') {
                $errorMsg = $prePayResult['err_code_des'].'('.$prePayResult['err_code'].')';
            }
        }

        if (!empty($errorMsg)) {
            Log::record('订单预支付失败：失败原因：'.json_encode($prePayResult, 320), 'error');
            throw new SefaException([
                'code' => 400,
                'message' => '支付失败，失败原因：'.$errorMsg,
                'errorCode' => 6004
            ]);
        } else {
            //如果预支付成功，保存微信返回的 prepay_id 到 order 表，然后拼凑前端支付需要的参数进行返回
            OrderInfo::where('id', '=', $this->orderID)->update(['prepay_id' => $prePayResult['prepay_id']]);

            return $this->getJsPaySignature($prePayResult);
        }
    }

    private function getJsPaySignature($prepayResult)
    {
        $jsApiData = new \WxPayJsApiPay();
        $jsApiData->SetAppid(config('wx.app_id'));
        $jsApiData->SetTimeStamp((string)time());
        $jsApiData->SetNonceStr(md5(time().mt_rand(100, 999)));
        $jsApiData->SetPackage('prepay_id='.$prepayResult['prepay_id']);
        $jsApiData->SetSignType('md5');

        $sign = $jsApiData->MakeSign(); //其中 key 值后缀后再加密

        $rawValues = $jsApiData->GetValues();   //将对象转换为数组形式

        $rawValues['sign'] = $sign; //原始数据中加入签名

        unset($rawValues['app_id']);

        return $rawValues;
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