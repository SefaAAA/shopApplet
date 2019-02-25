<?php

namespace app\api\controller\v1;

use app\api\model\OrderInfo;
use app\api\service\Order as OrderService;
use app\api\service\Token as TokenService;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\SefaException;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getOrderByUser,getDetail, cancel']
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

        $orders = OrderInfo::getSummaryByUser($uid, $page);

        $hidden = ['prepay_id', 'consignee', 'mobile', 'country', 'province', 'city', 'district', 'detail'];

        return $orders->isEmpty() ? [] : $orders->hidden($hidden)->toArray();
    }

    public function getDetail($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $orderDetail = OrderInfo::get($id, 'orderProduct');

        $orderDetail->status_zh = $orderDetail->status_zh;

        if (is_null($orderDetail)) {
            throw new SefaException([
                'code' => 404,
                'message' => '订单信息不存在',
                'errorCode' => 5002
            ]);
        }

        return $orderDetail->hidden(['prepay_id']);
    }

    public function cancel($id)
    {
        (new IDMustBePositiveInt())->goCheck();
        // 取消订单之前要检测权限以及订单是否是本人订单
        $info = OrderInfo::where(['id' => $id])->field('user_id,status')->find();

        if ($info->status != OrderStatusEnum::UNPAID) {
            throw new SefaException([
                'code' => 400,
                'message' => '订单不能被取消',
                'errorCode' => 5003
            ]);
        }

        $operationValid = TokenService::isValidOperate($info->user_id);

        if ($operationValid) {
            $res = OrderInfo::update(['id' => $id, 'status' => OrderStatusEnum::CANCELED]);
        } else {
            throw new SefaException([
                'code' => 403,
                'message' => '非本人订单，禁止操作',
                'errorCode' => 1004
            ]);
        }

        return json([
            'msg' => '成功取消订单'
        ], 202);
    }
}