<?php

namespace app\api\model;

class Image extends BaseModel
{
    protected $hidden = ['id', 'from', 'update_time', 'delete_time'];

    protected function getUrlAttr($url, $data)
    {
        return $this->getFullImgPath($url, $data);
    }
}
