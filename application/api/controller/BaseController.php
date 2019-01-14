<?php
/**
 * 控制器基类，用于集中所有控制器公用的前置操作等，比如操作权限检测
 */

namespace app\api\controller;

use think\Controller;

class BaseController extends Controller
{

    public function checkPrimaryScope()
    {

    }

    public function checkExclusiveScope()
    {

    }
}
