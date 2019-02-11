<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/15
 * Time: 17:14
 */

namespace app\lib\enum;


class OrderStatusEnum
{
    const CANCELED = -1;  //被取消

    const UNPAID = 1;   //未支付

    const PAID = 2; //已支付

    const PAID_BUT_UNSTOCK = 3; //已支付（库存不足）

    const DELIVERED = 4;    //已发货

    const FINISHED = 5; //收货完成

    static $orderStatus = [

        self::CANCELED => '被取消',

        self::UNPAID => '未支付',

        self::PAID => '已支付',

        self::PAID_BUT_UNSTOCK => '已支付（库存不足）',

        self::PAID_BUT_UNSTOCK => '已发货',

        self::FINISHED => '已完成',
    ];

}