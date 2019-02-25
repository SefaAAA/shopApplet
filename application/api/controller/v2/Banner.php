<?php

namespace app\api\controller\v1;

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

        if (is_null($banner)) {   //Model 中使用 find 方法查找单条数据如果结果为空就是 null，但是使用 select 查询数据列表时是否为空则需要借助于模型方法 isEmpty 进行判断
            throw new SefaException([
                'code' => 404,
                'message' => '没有找到 Banner 资源',
                'errorCode' => 2000,
            ]);
        }
        return $banner;
    }
}
