<?php

namespace app\api\model;

class OrderInfo extends BaseModel
{
    protected $autoWriteTimestamp = true;

    protected $insert = [
        'status' => 1,
    ];

    public function orderGoods()
    {
        return $this->hasMany('OrderProduct', 'order_id', 'id');
    }
}
