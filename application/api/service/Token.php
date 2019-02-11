<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/9
 * Time: 21:18
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\SefaException;
use think\Cache;
use think\Exception;
use app\api\model\User as UserModel;
use think\Request;

class Token extends BaseService
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;

    /**
     * Token constructor.
     * @param string $code
     */
    public function __construct($code = '')
    {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(config('wx.login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);
    }

    /**
     * 获取用户令牌
     * @return mixed
     * @throws Exception
     * @throws SefaException
     */
    public function get()
    {
        $loginRes = curl($this->wxLoginUrl);

        $loginRes = json_decode($loginRes, true);

        if (empty($loginRes)) {
            throw new Exception('获取微信登录态失败，失败原因：请求失败');
        } else {
            if (array_key_exists('openid', $loginRes) && !empty($loginRes['openid'])) {
                return $this->grantToken($loginRes);
            } else {
                if (array_key_exists('errcode', $loginRes) && $loginRes['errcode'] != 0) {
                    throw new SefaException([
                        'code' => 400,
                        'message' => '获取微信登录态失败，失败原因：'.$loginRes['errmsg'],
                        'errorCode' => $loginRes['errcode']
                    ]);
                }
            }
        }
    }

    /**
     * 根据 code 换取的登录态信息创建本地用户登录态信息并保存到缓存
     * @param array $loginRes 微信授权登录返回的结果集
     * @return mixed 返回生成的用户令牌（token）
     */
    public function grantToken($loginRes)
    {
        $openid = $loginRes['openid'];

        $user = UserModel::getByOpenid($openid);

        // 判断用户是否已经存在，如果不存在则新增该用户
        if (is_null($user)) {
            //此处有类似于下单“超单”的问题，暂不处理
            $maxID = UserModel::order('id desc')->limit(1)->value('id');
            $nicknameSuffix = empty($maxID) ? 1 : $maxID + 1;

            $newUser = UserModel::create([
                'openid' => $openid,
                'nickname' => '零零妖_'.$nicknameSuffix
            ]);
            $uid = $newUser->id;
        } else {
            $uid = $user->id;
        }
        //写入缓存
        $token = $this->saveToCache($loginRes, $uid);
        return $token;
    }

    /**
     * @param $loginRes
     * @param $uid
     * @return string
     */
    public function saveToCache($loginRes, $uid)
    {
        $cacheValue = array_merge($loginRes, [
            'uid' => $uid,
//            'scope' => ScopeEnum::User, //缓存身份权限标识
            'scope' => 15, //缓存身份权限标识
        ]);
        $value = json_encode($cacheValue);
        $key = self::generateToken();
        $expireIn = config('setting.token_expire_in');

        cache($key, $value, $expireIn);
        return $key;
    }

    /**
     * 获取用户 Token 令牌相关的某个信息
     * @param $key
     * @return mixed
     * @throws Exception
     * @throws SefaException
     */
    public static function getCurrentUserTokenVar($key)
    {
        $token = Request::instance()->header('token');

        $exceptionInfo = [
            'code' => 401,
            'message' => 'token 无效或已经过期',
            'errorCode' => 1002
        ];
        if (empty($token)) {
            throw new SefaException($exceptionInfo);
        }
        $cacheToken = Cache::get($token);

        if (empty($cacheToken)) {
            throw new SefaException($exceptionInfo);
        }

        if (!is_array($cacheToken)) {
            $cacheToken = json_decode($cacheToken, true);
        }

        if (!array_key_exists($key, $cacheToken)) {
            throw new Exception('获取用户 Token 信息时传递的键 '.$key.' 不存在');
        }

        return $cacheToken[$key];
    }

    public static function getCurrentUID()
    {
        return self::getCurrentUserTokenVar('uid');
    }

    /**
     * 前端小程序用户和管理员都能够访问的接口限制
     * @throws Exception
     * @throws SefaException
     */
    public static function needPrimaryScope()
    {
        $scope = self::getCurrentUserTokenVar('scope');

        if ($scope < ScopeEnum::User) {
            throw new ForbiddenException();
        }
    }

    /**
     * 不能让管理员访问的接口限制，exclusive 译为：排它的， 独有的
     * @throws Exception
     * @throws SefaException
     */
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentUserTokenVar('scope');

        if ($scope != ScopeEnum::User) {
            throw new ForbiddenException();
        }
    }

    public static function isValidOperate($dataUID)
    {
        $currentUID = self::getCurrentUID();

        return $dataUID == $currentUID ? true : false;
    }

    public static function verifyToken($token)
    {
        $userToken = Cache::get('token') == $token;
    }
}