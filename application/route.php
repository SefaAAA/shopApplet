<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

// 动态注册路由
Route::get('api/:version/banner/:id', 'api/:version.banner/getbanner');

Route::get('api/:version/theme', 'api/:version.theme/getsimplelist');
Route::get('api/:version/theme/:id', 'api/:version.theme/getcomplexone');

Route::group('api/:version/product', function() {
    Route::get('recent', 'api/:version.product/getrecent');
    Route::get('by_category/:id', 'api/:version.product/getbycatid');
    Route::get(':id', 'api/:version.product/getone');
}, [], ['id' => '\d+']);

Route::get('api/:version/category', 'api/:version.category/getallcategories');

Route::post('api/:version/token/user', 'api/:version.token/gettoken');
Route::post('api/:version/token/verify', 'api/:version.token/verify');

Route::post('api/:version/address', 'api/:version.address/createupdateuseraddress');
Route::get('api/:version/address', 'api/:version.address/getUserAddress');

Route::post('api/:version/order', 'api/:version.order/placeOrder');
Route::get('api/:version/order/by_user', 'api/:version.order/getorderbyuser');
Route::get('api/:version/order/detail', 'api/:version.order/getdetail');
Route::post('api/:version/order/cancel/:id', 'api/:version.order/cancel');

Route::post('api/:version/pay/prepay', 'api/:version.pay/getPrepayOrder');
Route::post('api/:version/pay/notify', 'api/:version.pay/receiveNotify');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
