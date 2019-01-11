<?php
/**
 * Created by PhpStorm.
 * User: Sefa
 * Date: 2019/1/5
 * Time: 22:08
 */

namespace app\api\validate;


use app\lib\exception\SefaException;
use think\Request;
use think\Validate;

class BaseValidate extends Validate
{
    public function goCheck()
    {
        $params = Request::instance()->param();

        $result = $this->batch()->check($params);

        if (!$result) {
            $errors = '参数错误：';
            foreach ($this->error as $error) {
                $errors .= '['.$error.'] ';
            }
            $this->error = rtrim($errors, ' ');

            throw new SefaException([
                'code' => 400,
                'message' => $this->error,
                'errorCode' => '1000'
            ]);
        } else {
            return true;
        }
    }

    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
            return $field.'必须是正整数';
        }
    }

    protected function checkIDs($ids)
    {
        $ids = explode(',', $ids);

        if (empty($ids)) {
            return false;
        }

        foreach($ids as $id) {
            if (!$this->isPositiveInteger($id)) {
                return false;
            }
        }
        return true;
    }

    protected function notEmpty($value)
    {
        return !empty($value);
    }
}