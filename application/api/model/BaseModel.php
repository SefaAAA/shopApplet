<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    protected function getFullImgPath($url, $data)
    {
        if ($data['from'] == 1) {
            return config('setting.img_prefix').$url;
        }
        return $url;
    }
}
