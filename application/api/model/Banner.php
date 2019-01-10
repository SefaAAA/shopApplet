<?php

namespace app\api\model;

use think\Model;

class Banner extends Model
{
    protected $hidden = ['update_time', 'delete_time'];
    /**
     * 声明 Banner 模型 BannerItem 模型之间的关联关系
     * @return \think\model\relation\HasMany
     */
    public function items()
    {
        return $this->hasMany('BannerItem', 'banner_id', 'id');
    }

    public static function getBannerByID($id)
    {
        return self::with('items.img')->find($id);
    }
}
