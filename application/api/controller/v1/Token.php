<?php

namespace app\api\controller\v1;

use app\api\validate\TokenGet;
use app\lib\exception\SefaException;
use think\Controller;
use app\api\service\Token as TokenService;

class Token extends Controller
{
    public function getToken($code = '')
    {
        (new TokenGet())->goCheck();

        $tokenService = new TokenService($code);
        $token = $tokenService->get($code);

        return ['token' => $token];
    }

    public function verify($token)
    {
        if (!$token) {
            throw new SefaException([
                'message' => 'token 不允许为空'
            ]);
        }

        return [
            'valid' => TokenService::verifyToken($token)
        ];
    }
}
