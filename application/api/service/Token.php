<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/9
 * Time: 21:18
 */

namespace app\api\service;


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
     * @param $loginRes
     * @return mixed
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
            'scope' => 16
        ]);
        $value = json_encode($cacheValue);
        $key = self::generateToken();
        $expireIn = config('setting.token_expire_in');

        cache($key, $value, $expireIn);
        return $key;
    }

    /**
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
            'message' => 'token 有误',
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
            throw new Exception('获取用户 Token 信息时传递的 key 不存在');
        }

        return $cacheToken[$key];
    }

    public static function getCurrentUID()
    {
        return self::getCurrentUserTokenVar('uid');
    }
}