<?php
/**
 * 控制器基类，用于集中所有控制器公用的前置操作等，比如操作权限检测
 */

namespace app\api\controller\v1;

use think\Controller;
use app\api\service\Token as TokenService;

class BaseController extends Controller
{

    public function checkPrimaryScope()
    {
        TokenService::needPrimaryScope();
    }

    public function checkExclusiveScope()
    {
        TokenService::needExclusiveScope();
    }
}
