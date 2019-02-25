<?php

namespace app\api\controller\v1;

use app\api\validate\IDCollection;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\SefaException;
use think\Controller;
use app\api\model\Theme as ThemeModel;

class Theme extends Controller
{
    /**
     * 根据给定的专题 IDs 获取专题列表信息
     * @param string $ids 专题 ID列表，形式：ids=1,3,4,...
     * @return Object
     */
    public function getSimpleList($ids)
    {
        (new IDCollection())->goCheck();

        $ids = explode(',', $ids);

        $themeList = ThemeModel::with('topicImg,headImg')->select($ids);    //with 方法的参数也可以是一个数组，当时字符串的时候，逗号隔开的字符串的中间不能有空格，否则会报错程序终止

        if (empty($themeList)) {
            throw new SefaException([
                'code' => 404,
                'message' => '请求的专题资源不存在',
                'errorCode' => 3000
            ]);
        }

        return $themeList;
    }

    public function getComplexOne($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $info = ThemeModel::getThemeWithProducts($id);

        return $info;
    }
}
