<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/13
 * Time: 14:07
 */

namespace app\api\service;
use app\api\model\OrderInfo;
use app\api\model\Product as ProductModel;
use app\api\model\UserAddress;
use app\lib\exception\SefaException;
use think\Exception;

class Order
{
    protected $oProducts;   //前端提交的订单信息

    protected $products;    //根据提交的订单信息从数据库查询到的商品信息

    protected $uid;

    //下单业务操作
    public function place($uid, $oProducts)
    {
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $this->uid = $uid;

        $orderStatus = $this->getOrderStatus(); //检测库存量的同时获取订单信息！

        if (!$orderStatus['pass']) {    //库存量检测失败，返回订单 ID 为 -1，表示创建订单失败
            $orderStatus['order_id'] = -1;
            return $orderStatus;
        }

        //整理订单信息，订单信息初始化
        $orderInfo = [
            'order_amount' => 0, //订单商品总数量
            'goods_amount' => 0,    //订单商品总金额
//            'product_status' => [],  //订单商品信息整理后的结果，和上面的 goods_amount 一样在库存检测时已经整理好了
            'consignee' => '',
            'mobile' => '', //以下几个字段是关于订单收货人的信息
            'country' => '',
            'province' => '',
            'city' => '',
            'district' => '',
            'address' => '',
//            'snap_name' => '',   //下面两个字段表示订单中如果包含多个商品在订单列表中显示的快照信息
//            'snap_img' => ''
        ];
        try {
            //整理订单的基本信息
            $orderInfo['user_id'] = $this->uid;
            $orderInfo['order_sn'] = $this->generateOrderSn();
            $orderInfo['total_count'] = $orderStatus['total_count'];
            $orderInfo['goods_amount'] = $orderStatus['goods_amount'];
    //        $orderInfo['product_status'] = $orderStatus['product_status'];  //订单中所有商品信息，没有按照视频教程得做饭存入数据库

            //收集收货地址信息，目前每个用户只能有一个收货地址
            $address = $this->getUserAddress();
            $orderInfo['consignee'] = $address['consignee'];
            $orderInfo['mobile'] = $address['mobile'];
            $orderInfo['country'] = $address['country'];
            $orderInfo['province'] = $address['province'];
            $orderInfo['city'] = $address['city'];
            $orderInfo['district'] = $address['district'];
            $orderInfo['detail'] = $address['detail'];

            //订单列表页订单显示快照信息
    //         $orderInfo['snap_name'] = $this->products[0]['name'];
    //         $orderInfo['snap_img'] = $this->products[0]['main_img_url'];
            $newOrder = OrderInfo::create($orderInfo);

            $orderId = $newOrder->id;

            $orderProducts = [];
            foreach ($orderStatus['product_status'] as $product) {
                $orderProduct['order_id'] = $orderId;
                $orderProduct['product_id'] = $product['id'];
                $orderProduct['product_name'] = $product['product_name'];
                $orderProduct['product_price'] = $product['price'];
                $orderProduct['product_number'] = $product['count'];
                array_push($orderProducts, $orderProduct);
            }

            $newOrder->order_goods()->saveAll($orderProducts);
        } catch (Exception $ex) {
            throw $ex;
        }

        return [
            'pass' => true,
            'order_id' => $newOrder->id,
            'order_sn' => $newOrder->order_sn,
            'create_time' => $newOrder->create_time,
        ];
    }

    public function generateOrderSn()
    {
        list($sec, $mssec) = explode(' ', microtime());
        $mssecPart = floor($mssec * 1000);
        return date('YmdHis').$mssecPart.mt_rand(100, 999);
    }

    /**
     * 获取当前用户的收货地址
     * @return array
     * @throws Exception
     * @throws SefaException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();

        if (is_null($userAddress)) {
            throw new SefaException([
                'code' => 404,
                'message' => '用户收货地址不存在',
                'errorCode' => 5001
            ]);
        }

        return $userAddress->toArray();
    }

    /**
     *
     * @throws SefaException
     */
    private function getOrderStatus()
    {
        $status = [ //初始化订单对比结果数据
            'pass' => true, //库存量检测是否通过
            'goods_amount' => 0, //订单中商品总金额
            'product_status' => [],  //订单下面所有商品的详细信息
            'total_count' => 0  // 订单中所有商品的数量
        ];

        //用户提交的订单信息与数据库查询出来的数据进行对比检测库存量，同时对查询出的订单商品信息进行整理
        foreach ($this->oProducts as $oProduct) {
            $productStatus = $this->getProductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
            if (!$productStatus['stock_enough']) {
                $status['pass'] = false;    //库存检测标识为未通过
            }
            $status['goods_amount'] += $productStatus['total_price'];

            array_push($status, $productStatus);
        }

        return $status;
    }

    /**
     * 对订单信息中单个商品的状态信息进行整理
     * @param int $oProductID 订单商品 ID
     * @param int $oCount 订单商品下单数量
     * @param array $products 根据订单信息从数据库查询出的所有商品信息
     * @return array 整理后的单个商品信息
     * @throws SefaException
     */
    private function getProductStatus($oProductID, $oCount, $products)
    {
        $productStatus = [  //初始化订单中单个商品信息数据
            'id' => null,
            'stock_enough' => false,
            'count' => 0,
            'name' => '',
            'price' => 0,   //记录商品价格方便后面的 order_product 表入库
            'total_price' => 0
        ];

        $productExists = false; //假设数据库中没有该商品

        for($i=0; $i<count($products); $i++) {
            if ($oProductID == $products[$i]['id']) {
                //如果数据库中查询出的商品中有该商品就整理该商品的信息
                $productExists = true;
                $productStatus['id'] = $products[$i]['id'];
                $productStatus['name'] = $products[$i]['name'];
                $productStatus['count'] = $oCount;  //购买的商品数量
                $productStatus['price'] = $products[$i]['price'];
                $productStatus['total_price'] = $products[$i]['price'] * $oCount;
                $productStatus['stock_enough'] = $products[$i]['stock'] >= $oCount ? true : false;
                return $productStatus;
            }
        }

        if (!$productExists) {  //数据库中没有找到该商品
            throw new SefaException([
                'code' => 404,
                'message' => '没有找到商品，创建订单失败',
                'errorCode' => 5000
            ]);
        }
    }

    //根据用户提交的订单信息从数据库查询对应的商品信息
    private function getProductsByOrder($oProducts)
    {
//        循环查询数据库不可取！！！换种思路：先从提交的订单信息中获取 product_id 的集合，然后用 in 语句进行查询
//        foreach ($oProducts as $product) {
//            //code...循环查询数据库不可取！！！
//        }
        $oProductsIDs = [];
        foreach ($oProducts as $product) {
            array_push($oProductsIDs, $product['product_id']);
        }

        $products = ProductModel::all($oProductsIDs)
            ->visible(['id', 'name', 'price', 'stock', 'main_img_url'])
            ->toArray();

        return $products;
    }
}