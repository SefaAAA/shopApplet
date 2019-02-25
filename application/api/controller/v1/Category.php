<?php

namespace app\api\controller\v1;

use app\lib\exception\SefaException;
use think\Controller;
use app\api\model\Category as CategoryModel;

class Category extends Controller
{
    public function getAllCategories()
    {
        $allCategories = CategoryModel::all([], 'headImg');

        if ($allCategories->isEmpty()) {
            throw new SefaException([
                'code' => 404,
                'message' => '请求的分类列表不存在',
                'errorCode' => 5000
            ]);
        }
        return $allCategories;
    }
}
