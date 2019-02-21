<?php

namespace app\api\model;

use app\lib\enum\OrderStatusEnum;

class OrderInfo extends BaseModel
{
    protected $hidden = ['user_id', 'update_time', 'delete_time'];
    protected $autoWriteTimestamp = true;

    protected $insert = [
        'status' => 1,
    ];

    protected function getStatusZhAttr($value, $data)
    {
        return OrderStatusEnum::$orderStatus[$data['status']];  // 定义 status 对应的状态解释
    }

    public function orderProduct()
    {
        return $this->hasMany('OrderProduct', 'order_id', 'id');
    }

    /**
     * 查询用户的历史订单，同时关联订单商品表生成订单列表中的商品快照信息
     * @param $uid
     * @param int $page
     * @param int $size
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getSummaryByUser($uid, $page = 1, $size = 15)
    {
        //paginate 方法和 find、select 方法一样，会查询数据库返回一个模型对象
        $pagingData = self::with(['orderProduct' => function($query) {
//            $query->limit(2); //限制关联模型的查询数量有 Bug
        }])->where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, true, ['page' => $page])
            ->each(function($order) {
                $order['snap_name'] = $order->orderProduct[0]['product_name'];
                $order['snap_img'] = $order->orderProduct[0]['snap_img'];
                $order->status_zh = $order->status_zh;  // 将状态值转换成对应的中文
                $orderProductCount = 0;
                foreach ($order->orderProduct as $product) {
                    $orderProductCount += $product['product_number'];
                }
                $order['order_product_count'] = $orderProductCount;
            });

        return $pagingData;
    }

    public static function cancelOrder($id)
    {

    }
}
