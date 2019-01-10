<?php

namespace app\api\controller;

use app\api\validate\TokenGet;
use think\Controller;
use app\api\service\Token as TokenService;

class Token extends Controller
{
    public function getToken($code = '', TokenService $tokenService)
    {
        (new TokenGet())->goCheck();

        $token = $tokenService->get($code);

        return $token;
    }
}
