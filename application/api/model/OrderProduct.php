<?php

namespace app\api\model;


class OrderProduct extends BaseModel
{
    protected function getSnapImgAttr($url, $data) {

        return $this->getFullImgPath($url, $data);
    }

}
