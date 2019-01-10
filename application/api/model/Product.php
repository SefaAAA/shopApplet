<?php

namespace app\api\model;

class Product extends BaseModel
{
    protected $hidden = ['cat_id', 'from', 'cat_id', 'img_id', 'pivot', 'update_time', 'delete_time'];

    public function getMainImgUrlAttr($url, $data)
    {
        return $this->getFullImgPath($url, $data);
    }

    public static function getMostRecent($count)
    {
        return self::order('create_time desc')
            ->limit($count)
            ->select();
    }

    public static function getProductsByCatID($catId)
    {
        return self::where('cat_id', '=', $catId)
            ->select();
    }
}
