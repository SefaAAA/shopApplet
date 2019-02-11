<?php

namespace app\api\model;

use think\Model;

class UserAddress extends Model
{
    protected $hidden = ['id', 'user_id', 'update_time', 'delete_time'];

//    protected $autoWriteTimestamp = true;
}
