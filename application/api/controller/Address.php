<?php

namespace app\api\controller;

use app\api\validate\AddressInfo;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\SefaException;
use think\Controller;
use app\api\service\Token as TokenService;
use app\api\model\User as UserModel;

class Address extends Controller
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createUpdateUserAddress'],
    ];

    /**
     * 访问某些接口之前需要预先检查权限
     * @throws ForbiddenException
     * @throws SefaException
     * @throws \think\Exception
     */
    public function checkPrimaryScope()
    {
        $scope = TokenService::getCurrentUserTokenVar('scope');

        if ($scope < ScopeEnum::User) {
            throw new ForbiddenException();
        }
    }

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
