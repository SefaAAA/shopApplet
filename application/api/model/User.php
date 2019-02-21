<?php
namespace app\api\model;

class User extends BaseModel
{
    protected $autoWriteTimestamp = true;

    public function address()
    {
        return $this->hasOne('UserAddress', 'user_id', 'id');
    }

    public static function getByOpenid($openid)
    {
        $res = self::where('openid', '=', $openid)
            ->find();
        return $res;
    }
}