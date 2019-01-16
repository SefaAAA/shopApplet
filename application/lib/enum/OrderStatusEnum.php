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
    const UNPAID = 1;   //未支付

    const PAID = 2; //已支付

    const PAID_BUT_UNSTOCK = 3; //已支付但库存量不足

    const DELIVERED = 4;    //已经发货

    const FINISHED = 5;  //收货完成

}