<?php

namespace app\api\model;

class OrderInfo extends BaseModel
{
    protected $autoWriteTimestamp = true;

    protected $insert = [
        'status' => 0,
    ];

    public function orderGoods()
    {
        return $this->hasMany('OrderProduct', 'order_id', 'id');
    }
}