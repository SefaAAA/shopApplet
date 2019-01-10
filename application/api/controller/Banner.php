<?php

namespace app\api\controller;

use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\SefaException;
use think\Controller;
use app\api\model\Banner as BannerModel;

class Banner extends Controller
{
    public function getBanner($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $banner = BannerModel::getBannerByID($id);

        if (empty($banner)) {
            throw new SefaException([
                'code' => 404,
                'message' => '没有找到 Banner 资源',
                'errorCode' => 2000,
            ]);
        }
        return $banner;
    }
}
