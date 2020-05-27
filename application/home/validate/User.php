<?php
/**
 * Date: 2019/5/7
 * Time: 10:37
 */

namespace application\home\validate;


use library\mysmarty\Validate;

class User extends Validate
{
    protected $rule = [
        'username@用户名' => 'trim|includeName:abc|required',
        'age@年龄' => 'int|between:10,40',
    ];

    /**
     * 验证用户名是否包含 abc 字符串
     * @param mixed $data 字段的值，表单传过来的
     * @param string $label 字段的标签，@后面的中文字段说明
     * @param string $param 规则的参数，:后面的值
     * @return bool
     */
    protected function includeName(&$data, $label, $param)
    {
        if (preg_match('/' . $param . '/i', $data)) {
            return true;
        }
        $this->setError($label . '不包含abc');
        return false;
    }
}