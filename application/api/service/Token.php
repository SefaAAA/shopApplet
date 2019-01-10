<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/9
 * Time: 21:18
 */

namespace app\api\service;


use app\lib\exception\SefaException;
use think\Exception;

class Token
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
        $this->wxLoginUrl = sprintf($this->wxLoginUrl, $this->wxAppID, $this->wxAppSecret, $this->code);
    }

    public function get()
    {
        $loginRes = curl($this->wxLoginUrl);

        $loginRes = json_decode($loginRes, true);

        if (empty($loginRes)) {
            throw new Exception('获取微信登录态失败，失败原因，请求失败');
        } else {
            if (array_key_exists('errcode', $loginRes)) {
                if (!empty($loginRes['errcode'])) {
                    throw new SefaException([
                        'code' => 403,
                        'message' => '获取微信登录态失败，失败原因：'.$loginRes['errmsg'],
                        'errorCode' => $loginRes['errcode']
                    ]);
                } else {
                    return $this->grantToken($loginRes);
                }
            } else {
                throw new Exception('获取微信登录态失败，失败原因：微信返回结果中没有错误码');
            }
        }
    }

    public function grantToken($loginRes)
    {
        $openid = $loginRes['openid'];

        return $openid;
    }
}