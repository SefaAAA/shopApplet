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
    /**
     * 定义控制器参数统一验证方法
     * @param bool $filter 是否需要同时返回过滤后的请求参数
     * @return array|bool
     * @throws SefaException
     */
    public function goCheck($filter = false)
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
        }
        //如果需要可以同时返回过滤后的数据
        if ($filter) {
            return $this->getDataByRule($params);
        }

        return true;
    }

    /**
     * 参照验证器定义的规则过滤用户请求参数，包括 GET、POST、PUT 三种请求方式
     * @param array $originData 所有的请求参数，相当于$_REQUEST
     * @return array 过滤后的请求参数
     * @throws SefaException
     */
    protected function getDataByRule($originData)
    {
        if (array_key_exists('user_id', $originData) || array_key_exists('uid', $originData)) {
            $uidField = $originData['user_id'] ? 'user_id' : 'uid';
            throw new SefaException([
                'message' => '参数中含有非法参数'.$uidField
            ]);
        }

        $validData = [];

        foreach ($this->rule as $key => $value) {
            $validData[$key] = $originData[$key];
        }

        return $validData;
    }

    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
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