<?php
namespace app\api\model;

class User extends BaseModel
{
    protected $autoWriteTimestamp = true;

    public static function getByOpenid($openid)
    {
        $res = self::where('openid', '=', $openid)
            ->find();
        return $res;
    }
}