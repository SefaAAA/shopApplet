<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    protected function getFullImgPath($url, $data)
    {
        //from 标识图片来自网络还是本地
        if ($data['from'] == 1) {
            return config('setting.img_prefix').$url;
        }
        return $url;
    }
}
