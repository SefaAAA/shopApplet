<?php

namespace app\api\controller;

use app\api\model\User as UserModel;
use app\api\service\Token as TokenService;
use app\api\validate\AddressInfo;
use app\lib\exception\SefaException;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createUpdateUserAddress'],
    ];

    public function createUpdateUserAddress()
    {
        $data = (new AddressInfo())->goCheck(true);

        //根据用户Token获取用户信息，然后判断用户地址是否存在，如果存在则更新，不存在则新增
        $uid = TokenService::getCurrentUID();

        $user = UserModel::get($uid);
        if (is_null($user)) {
            throw new SefaException([
                'code' => 401,
                'message' => '用户不存在',
                'errorCode' => 1003
            ]);
        }

        if (empty($user->address)) {
            $user->address()->save($data);
        } else {
            $user->address->save($data);
        }
    }
}
