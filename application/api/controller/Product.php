<?php

namespace app\api\controller;

use app\api\validate\Count;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\SefaException;
use think\Controller;
use app\api\model\Product as ProductModel;

class Product extends Controller
{
    public function getRecent($count = 15)
    {
        (new Count())->goCheck();

        $productList = ProductModel::getMostRecent($count);

        $productList = $productList->hidden(['summary']);    //只有数据库配置文件中配置结果集为对象类型时，才会有 hidden、visible 等这样的处理方法

//        if (empty($productList)) {    //结果集类型为数组时可以直接这样判断，如果配置的结果集为对象类型时就需要使用 isEmpty 方法进行判断
        if ($productList->isEmpty()) {
            throw new SefaException([
                'code' => 404,
                'message' => '请求的产品列表不存在',
                'errorCode' => '4000'
            ]);
        }
        return $productList;
    }

    public function getByCatID($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $productList = ProductModel::getProductsByCatID($id);

        if ($productList->isEmpty()) {
            throw new SefaException([
                'code' => 404,
                'message' => '指定分类的商品列表不存在',
                'errorCode' => 4001
            ]);
        }

        $productList->hidden(['summary']);
        return $productList;
    }

    public function getOne($id)
    {
        (new IDMustBePositiveInt())->goCheck();

        $info = ProductModel::getDetail($id);

        if (is_null($info)) {
            throw new SefaException([
                'code' => 404,
                'message' => '指定商品不存在',
                'errorCode' => 4002
            ]);
        }

        return $info;
    }

    public function deleteOne($id)
    {

    }
}
