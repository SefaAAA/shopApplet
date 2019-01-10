<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/6
 * Time: 14:26
 */

namespace app\lib\exception;


use Exception;
use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle
{
    private $code;
    private $message;
    private $errorCode;
    /**
     * 重写异常信息渲染方法
     * @param Exception $e
     * @return \think\Response|void
     */
    public function render(Exception $e)
    {
        if ($e instanceof SefaException) {
            //RESTFul API 自定义异常
            $this->code = $e->code;
            $this->message = $e->msg;
            $this->errorCode = $e->errorCode;
        } else {
            //服务器内部异常
            if (config('app_debug')) {
                return parent::render($e);  // 必须要要 return 出去
            } else {
                $this->code = 500;
                $this->message = '服务器内部错误';
                $this->errorCode = 999;

                Log::record($e->getMessage(), 'error');
            }
        }

        $result = [
            'message' => $this->message,
            'code' => $this->errorCode,
            'request_url' => Request::instance()->url()
        ];
        return json($result, $this->code);
    }
}