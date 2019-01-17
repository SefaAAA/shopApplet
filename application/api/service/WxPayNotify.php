<?php
/**
 * 继承自微信支付官方 SDK 提供的异步通知类（使用其预留的主方法）
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/16
 * Time: 15:44
 */

namespace app\api\service;

use app\api\model\OrderInfo;
use app\api\model\Product as ProductModel;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;
use app\api\service\Order as OrderService;

Loader::import('WxPay.WxPay', EXTEND_PATH, 'Api.php');

class WxPayNotify extends \WxPayNotify
{
    //重写回调处理函数
    /**
     * @param WxPayNotifyResults $data 回调解释出的参数
     * @param WxPayConfigInterface $config
     * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
     * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
     */
    public function NotifyProcess($objData, $config, &$msg)
    {
        $data = $objData->GetValues();
        //结果验证
        if(!array_key_exists("return_code", $data) ||(array_key_exists("return_code", $data) && $data['return_code'] != "SUCCESS")) {
            $msg = "异常错误";
        }

        //验证签名
        /*$checkResult = $objData->CheckSign($config);
        if($checkResult == false){
            //TODO 签名不通过
        }*/

        $orderSn = $data['out_trade_no'];
        Db::startTrans();   //开启事务处理
        try {
            //检测订单状态
            $order = OrderInfo::where('order_sn', '=', $orderSn)
                ->lock()    //锁定该条查询语句
                ->find();
            if (is_null($order)) {
                $msg = '订单' . $orderSn . '不存在';
            }

            if ($order->status != 1) {
                $msg = '订单' . $orderSn . '已经支付过了';
            }

            if (!empty($msg)) {
                Log::error($msg);
                return true;
            }

            $orderStatus = (new OrderService())->checkOrderStock($order->id);

            if ($orderStatus['pass']) {
                $this->updateOrderStatus($order->id, true);
                $this->reduceStock($orderStatus['product_status']);
            } else {
                $this->updateOrderStatus($order->id, false);
            }
            Db::commit();
            return true;
        } catch (Exception $ex) {
            Db::rollback();
            Log::error('订单异步通知处理失败：失败原因：'.$ex->getMessage());
            return false;
        }
        //查询订单，判断订单真实性
        /*if(!$this->Queryorder($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }*/
    }

    /**
     * 根据订单中商品的商品数量进行减库存
     * @param $orderProducts
     * @throws \think\Exception
     */
    private function reduceStock($orderProducts)
    {
        foreach ($orderProducts as $product) {
            ProductModel::where('stock', '=', $product['id'])->setDec('stock', $product['count']);
        }
    }

    /**
     * 根据库存量检测结果修改订单状态
     * @param $orderID
     * @param bool $stockPass
     */
    private function updateOrderStatus($orderID, $stockPass = true)
    {
        $status = $stockPass == true ? OrderStatusEnum::PAID : OrderStatusEnum::PAID_BUT_UNSTOCK;

        OrderInfo::where('id', '=', $orderID)->update(['status' => $status]);
    }
}