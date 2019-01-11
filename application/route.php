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
Route::get('banner/:id', 'api/banner/getbanner');

Route::get('theme', 'api/theme/getsimplelist');
Route::get('theme/:id', 'api/theme/getcomplexone');

Route::group('product', function() {
    Route::get('recent', 'api/product/getrecent');
    Route::get('by_category/:id', 'api/product/getbycatid');
    Route::get(':id', 'api/product/getone');
}, [], ['id' => '\d+']);

Route::get('category', 'api/category/getallcategories');

Route::post('token/user', 'api/token/gettoken');

Route::post('address', 'api/address/createupdateuseraddress');

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

];
