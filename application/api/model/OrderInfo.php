<?php

namespace app\api\model;

class OrderInfo extends BaseModel
{
    protected $hidden = ['user_id', 'update_time', 'delete_time'];
    protected $autoWriteTimestamp = true;

    protected $insert = [
        'status' => 1,
    ];

    public function orderGoods()
    {
        return $this->hasMany('OrderProduct', 'order_id', 'id');
    }

    public static function getSummaryByUser($uid, $page = 1, $size = 15)
    {
        //paginate 方法和 find、select 方法一样，会查询数据库返回一个模型对象
        $pagingData = self::where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }
}
