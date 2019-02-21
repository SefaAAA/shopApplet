<?php

namespace app\api\model;

class Product extends BaseModel
{
    protected $hidden = ['cat_id', 'from', 'cat_id', 'img_id', 'pivot', 'update_time', 'delete_time'];

    /**
     * 一个商品详情中有多张图片
     * @return \think\model\relation\HasMany
     */
    public function imgs()
    {
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }

    /**
     * 一个商品具有多个特性描述
     * @return \think\model\relation\HasMany
     */
    public function properties()
    {
        return $this->hasMany('ProductProperty', 'product_id', 'id');
    }

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

    public static function getDetail($id)
    {
        //获取商品信息的时候需要对商品详情页的图片进行排序处理，这里涉及到关联预载入条件限制的技能
        return self::with('properties')
            ->with(['imgs' => function($query) {
                $query->with('img')
                    ->order('order desc');
            }])
            ->find($id);
    }
}
