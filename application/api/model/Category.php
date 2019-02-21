<?php

namespace app\api\model;

class Category extends BaseModel
{
    protected $hidden = ['head_img_id', 'update_time', 'delete_time'];

    public function headImg()
    {
        return $this->belongsTo('Image', 'head_img_id', 'id');
    }
}
