<?php
/**
 * Date: 2019/5/7
 * Time: 10:34
 */

namespace library\mysmarty;

class Validate
{
    //验证规则
    protected array $rule = [];
    protected string $error = '';
    private array $labels = [];
    protected string $validateField = '';

    /**
     * 初始化变量
     * @return static
     */
    final public function init(): static
    {
        $this->rule = [];
        $this->error = '';
        $this->labels = [];
        $this->validateField = '';
        return $this;
    }

    /**
     * 验证规则
     * @param string $field 需要验证的表单字段名称，多个逗号分隔
     * @return bool
     */
    final public function run(string $field = ''): bool
    {
        if (empty($this->rule)) {
            return true;
        }
        $allRule = [];
        $allLabel = [];
        foreach ($this->rule as $k => $v) {
            $pos = strpos($k, '@');
            if ($pos !== false) {
                $k1 = substr($k, 0, $pos);
                $allLabel[$k1] = substr($k, $pos + 1);
            }
            $allRule[$k1] = $v;
        }
        $this->labels = $allLabel;
        if (empty($field)) {
            $fieldArr = array_merge(array_keys($_GET), array_keys($_POST), array_keys($_FILES));
        } else {
            $fieldArr = explode(',', $field);
        }
        if (empty($fieldArr)) {
            return true;
        }
        foreach ($fieldArr as $f) {
            if (!isset($allRule[$f])) {
                continue;
            }
            $this->validateField = $f;
            $rule = $allRule[$f];
            $ruleArr = explode('|', $rule);
            foreach ($ruleArr as $r) {
                if (empty($r)) {
                    continue;
                }
                $rArr = explode(':', $r);
                $rParam = $rArr[1] ?? '';
                $label = $this->getLabel($f);
                if (isset($_GET[$f])) {
                    $result = call_user_func_array([$this, $rArr[0]], [&$_GET[$f], $label, $rParam]);
                } else if (isset($_POST[$f])) {
                    $result = call_user_func_array([$this, $rArr[0]], [&$_POST[$f], $label, $rParam]);
                } else if (isset($_FILES[$f])) {
                    $result = call_user_func_array([$this, $rArr[0]], [&$_FILES[$f], $label, $rParam]);
                } else {
                    $this->setError('不支持非GET、POST、FILES方式的传输');
                    $result = false;
                }
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 必须存在，不能为空
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @return bool
     */
    final private function required(mixed &$data, string $label): bool
    {
        if (empty($data)) {
            $this->setError($label . '不能为空');
            return false;
        }
        return true;
    }

    /**
     * 获取字段说明
     * @param string $field
     * @return string
     */
    public function getLabel(string $field): string
    {
        if (isset($this->labels[$field])) {
            return $this->labels[$field];
        }
        return $field;
    }

    /**
     * 设置错误信息
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * 获取错误信息
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * 数值在数字之间
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function between(mixed &$data, string $label, string $param): bool
    {
        $paramArr = explode(',', $param);
        if ($data < $paramArr[0]) {
            $this->setError($label . '小于' . $paramArr[0]);
            return false;
        }
        if ($data > $paramArr[1]) {
            $this->setError($label . '大于' . $paramArr[1]);
            return false;
        }
        return true;
    }

    /**
     * 是否是数字
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function number(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[\d][\d\.]+$/U', $data)) {
            $this->setError($label . '不是一个数字');
            return false;
        }
        return true;
    }

    /**
     * 是否是整数
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function integer(mixed &$data, string $label, string $param): bool
    {
        if (!filter_var($data, FILTER_VALIDATE_INT)) {
            $this->setError($label . '不是一个整数');
            return false;
        }
        return true;
    }

    /**
     * 是否是浮点数
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function float(mixed &$data, string $label, string $param): bool
    {
        if (!filter_var($data, FILTER_VALIDATE_FLOAT)) {
            $this->setError($label . '不是一个浮点数');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为布尔值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function boolean(mixed &$data, string $label, string $param): bool
    {
        if (!filter_var($data, FILTER_VALIDATE_BOOLEAN)) {
            $this->setError($label . '不是一个布尔值');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为邮箱
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function email(mixed &$data, string $label, string $param): bool
    {
        if (!isEmail($data)) {
            $this->setError($label . '不是一个有效的邮箱账号');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为数组
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function array(mixed &$data, string $label, string $param): bool
    {
        if (!is_array($data)) {
            $this->setError($label . '不是数组');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为有效的时间
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function date(mixed &$data, string $label, string $param): bool
    {
        if (!strtotime($data)) {
            $this->setError($label . '不是一个有效的时间');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为字母
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function alpha(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[a-z]+$/i', $data)) {
            $this->setError($label . '不是一个有效的字母');
            return false;
        }
        return true;
    }


    /**
     * 验证某个字段的值是否为字母和数字
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function alphaNum(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[a-z0-9]+$/i', $data)) {
            $this->setError($label . '不是一个有效的字母或数字');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为字母和数字，下划线_及破折号-
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function alphaDash(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $data)) {
            $this->setError($label . '不是一个有效的字母或数字或下划线或破折号');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为汉字
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function chs(mixed &$data, string $label, string $param): bool
    {
        if (!is_string($data) || !isZh($data)) {
            $this->setError($label . '不是汉字');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为只能是汉字、字母
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function chsAlpha(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-z]+$/iu', $data)) {
            $this->setError($label . '不是汉字');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为只能是汉字、字母和数字
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function chsAlphaNum(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-z0-9]+$/iu', $data)) {
            $this->setError($label . '不是汉字');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为只能是汉字、字母和数字,下划线_及破折号-
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function chsDash(mixed &$data, string $label, string $param): bool
    {
        if (!preg_match('/^[\x{4e00}-\x{9fa5}a-z0-9_-]+$/iu', $data)) {
            $this->setError($label . '不是汉字');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为有效的域名或者IP
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function activeUrl(mixed &$data, string $label, string $param): bool
    {
        if (!checkdnsrr($data)) {
            $this->setError($label . '不是有效的域名或IP');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为有效的URL地址
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function url(mixed &$data, string $label, string $param): bool
    {
        if (!isUrl($data)) {
            $this->setError($label . '不是有效的URL地址');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为有效的IP地址
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function ip(mixed &$data, string $label, string $param): bool
    {
        if (!isIp($data)) {
            $this->setError($label . '不是有效的IP地址');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否为指定格式的日期
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function dateFormat(mixed &$data, string $label, string $param): bool
    {
        $time = strtotime($data);
        if (!$time || strtotime(date($param, strtotime($data))) !== $time) {
            $this->setError($label . '时间格式错误');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否在某个范围
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function in(mixed &$data, string $label, string $param): bool
    {
        $inArr = explode(',', $param);
        if (!in_array($data, $inArr, true)) {
            $this->setError($label . '应该是' . $param . '其中的一个值');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值不在某个范围
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function notIn(mixed &$data, string $label, string $param): bool
    {
        $inArr = explode(',', $param);
        if (in_array($data, $inArr, true)) {
            $this->setError($label . '不应该是' . $param . '其中的一个值');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值不在某个范围
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function notBetween(mixed &$data, string $label, string $param): bool
    {
        $paramArr = explode(',', $param);
        if ($data >= $paramArr[0] && $data <= $paramArr[1]) {
            $this->setError($label . '取值范围错误');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值的长度是否在某个范围
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function length(mixed &$data, string $label, string $param): bool
    {
        $paramArr = explode(',', $param);
        $error = false;
        if (is_array($data)) {
            if (count($paramArr) === 1) {
                if (count($data) !== $paramArr[0]) {
                    $error = true;
                }
            } else {
                if (count($data) < $paramArr[0] || count($data) > $paramArr[1]) {
                    $error = true;
                }
            }
        } else {
            $len = mb_strlen($data, 'utf-8');
            if (count($paramArr) === 1) {
                if ($len !== $paramArr[0]) {
                    $error = true;
                }
            } else {
                if ($len < $paramArr[0] || $len > $paramArr[1]) {
                    $error = true;
                }
            }
        }
        if ($error) {
            $this->setError($label . '长度错误');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值的最大值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function max(mixed &$data, string $label, string $param): bool
    {
        if ((int)$data > $param) {
            $this->setError($label . '超出限制值');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值的最小值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function min(mixed &$data, string $label, string $param): bool
    {
        if ((int)$data < $param) {
            $this->setError($label . '小于限制值');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否在某个日期之后
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function after(mixed &$data, string $label, string $param): bool
    {
        if (strtotime($data) <= strtotime($param)) {
            $this->setError($label . '不在' . $param . '之后');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否在某个日期之前
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function before(mixed &$data, string $label, string $param): bool
    {
        if (strtotime($data) >= strtotime($param)) {
            $this->setError($label . '不在' . $param . '之前');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段的值是否在某个日期之前
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function confirm(mixed &$data, string $label, string $param): bool
    {
        if (!isset($_REQUEST[$param]) || $_REQUEST[$param] !== $data) {
            $this->setError($label . '与' . $this->getLabel($param) . '输入不一致');
            return false;
        }
        return true;
    }

    /**
     * 验证某个字段是否和另外一个字段的值不一致
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function different(mixed &$data, string $label, string $param): bool
    {
        if (isset($_REQUEST[$param]) && $_REQUEST[$param] === $data) {
            $this->setError($label . '与' . $this->getLabel($param) . '输入一致');
            return false;
        }
        return true;
    }


    /**
     * 验证是否等于某个值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function eq(mixed &$data, string $label, string $param): bool
    {
        if ($param !== $data) {
            $this->setError($label . '与' . $param . '不相等');
            return false;
        }
        return true;
    }

    /**
     * 验证是否大于等于某个值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function egt(mixed &$data, string $label, string $param): bool
    {
        if ($data < $param) {
            $this->setError($label . '小于' . $param);
            return false;
        }
        return true;
    }

    /**
     * 验证是否大于等于某个值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function gt(mixed &$data, string $label, string $param): bool
    {
        if ($data <= $param) {
            $this->setError($label . '小于或等于' . $param);
            return false;
        }
        return true;
    }

    /**
     * 验证是否小于等于某个值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function elt(mixed &$data, string $label, string $param): bool
    {
        if ($data > $param) {
            $this->setError($label . '大于' . $param);
            return false;
        }
        return true;
    }

    /**
     * 验证是否小于某个值
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function lt(mixed &$data, string $label, string $param): bool
    {
        if ($data >= $param) {
            $this->setError($label . '大于或等于' . $param);
            return false;
        }
        return true;
    }

    /**
     * 验证是否是一个上传文件
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function file(mixed &$data, string $label, string $param): bool
    {
        if (!isset($_FILES[$this->validateField])) {
            $this->setError($label . '不是一个文件');
            return false;
        }
        return true;
    }

    /**
     * 验证是否是一个图像文件
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function image(mixed &$data, string $label, string $param): bool
    {
        if (!isset($_FILES[$this->validateField])) {
            $this->setError($label . '不是一个文件');
            return false;
        }
        $type = $data['type'];
        if (is_array($type)) {
            foreach ($type as $v) {
                if (empty($v) || 0 !== stripos($v, 'image')) {
                    $this->setError($label . '不是图片类型');
                    return false;
                }
            }
        } else {
            if (empty($type) || 0 !== stripos($type, 'image')) {
                $this->setError($label . '不是图片类型');
                return false;
            }
        }
        return true;
    }

    /**
     * 验证上传文件后缀
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function fileExt(mixed &$data, string $label, string $param): bool
    {
        if (!isset($_FILES[$this->validateField])) {
            $this->setError($label . '不是一个文件');
            return false;
        }

        if (empty($param)) {
            $this->setError($label . '允许的文件后缀为空');
            return false;
        }
        $result = true;
        $paramArr = explode(',', $param);
        $name = $data['name'];
        if (is_array($name)) {
            foreach ($name as $v) {
                $hz = pathinfo($v, PATHINFO_EXTENSION);
                if (empty($v) || !in_array($hz, $paramArr, true)) {
                    $result = false;
                    break;
                }
            }
        } else {
            $hz = pathinfo($name, PATHINFO_EXTENSION);
            if (empty($name) || !in_array($hz, $paramArr, true)) {
                $result = false;
            }
        }
        if (!$result) {
            $this->setError($label . '上传文件后缀出错');
            return false;
        }
        return true;
    }

    /**
     * 验证上传文件类型
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function fileMime(mixed &$data, string $label, string $param): bool
    {
        if (!isset($_FILES[$this->validateField])) {
            $this->setError($label . '不是一个文件');
            return false;
        }
        if (empty($param)) {
            $this->setError($label . '允许的文件类型为空');
            return false;
        }
        $result = false;
        $paramArr = explode(',', $param);
        $type = $data['type'];
        if (is_array($type)) {
            foreach ($type as $v) {
                if (!empty($v)) {
                    foreach ($paramArr as $t) {
                        $t = preg_quote($t, '/');
                        if (preg_match('/' . $t . '/i', $v)) {
                            $result = true;
                            break;
                        }
                    }
                } else {
                    $result = false;
                    break;
                }
            }
        } else {
            if (!empty($type)) {
                foreach ($paramArr as $t) {
                    $t = preg_quote($t, '/');
                    if (preg_match('/' . $t . '/i', $type)) {
                        $result = true;
                        break;
                    }
                }
            } else {
                $result = false;
            }
        }
        if (!$result) {
            $this->setError($label . '上传的文件类型错误');
            return false;
        }
        return true;
    }

    /**
     * 验证上传文件大小
     * @param mixed $data 字段的值
     * @param string $label 字段的标签
     * @param string $param 规则的参数
     * @return bool
     */
    final private function fileSize(mixed &$data, string $label, string $param): bool
    {
        if (!isset($_FILES[$this->validateField])) {
            $this->setError($label . '不是一个文件');
            return false;
        }
        if (empty($param)) {
            $this->setError($label . '允许的文件大小未设置');
            return false;
        }
        $result = true;
        $size = $data['size'];
        if (is_array($size)) {
            foreach ($size as $v) {
                if (empty($v) || $v > $param) {
                    $result = false;
                    break;
                }
            }
        } else {
            if (empty($v) || $size > $param) {
                $result = false;
            }
        }
        if (!$result) {
            $this->setError($label . '上传的文件大小超过' . $param);
            return false;
        }
        return true;
    }
}