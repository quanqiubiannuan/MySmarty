<?php

namespace library\mysmarty;

/**
 * 验证类
 * @package library\mysmarty
 */
class Validate
{
    // 验证规则
    protected array $rule = [];
    // 当前错误
    protected string $error = '';
    // 字段中文对应
    private array $labels = [];
    // 验证场景
    protected array $scene;
    // 当前场景
    private string $currentScene;
    // 当前验证的字段
    private string $validateField;

    /**
     * 验证场景
     * @param string $scene 场景
     * @return static
     */
    final public function scene(string $scene): static
    {
        $this->currentScene = $scene;
        return $this;
    }

    /**
     * 验证数据
     * @param array $data 待验证的数据
     * @return bool
     */
    final public function check(array $data): bool
    {
        if (empty($this->rule)) {
            return true;
        }
        $allRule = [];
        $allLabel = [];
        // 待验证的字段列表
        $validateFields = [];
        if (!empty($this->currentScene)) {
            $validateFields = $this->scene[$this->currentScene] ?? [];
            if (empty($validateFields)) {
                $this->setError('验证场景不存在');
                return false;
            }
            if (is_string($validateFields)) {
                $validateFields = explode(',', $validateFields);
            }
        }
        foreach ($this->rule as $k => $v) {
            $pos = strpos($k, '@');
            if ($pos !== false) {
                $k1 = substr($k, 0, $pos);
                $allLabel[$k1] = substr($k, $pos + 1);
            } else {
                $k1 = $k;
            }
            if (!empty($validateFields) && !in_array($k1, $validateFields)) {
                continue;
            }
            $allRule[$k1] = $v;
        }
        $this->labels = $allLabel;
        foreach ($allRule as $f => $rule) {
            $label = $this->getLabel($f);
            if (!isset($data[$f])) {
                $this->setError($label . '不存在');
                return false;
            }
            $this->validateField = $f;
            $ruleArr = explode('|', $rule);
            foreach ($ruleArr as $r) {
                $r = myTrim($r);
                if (empty($r)) {
                    continue;
                }
                $rArr = explode(':', $r);
                $rParam = $rArr[1] ?? '';
                $result = call_user_func_array([$this, $rArr[0]], [&$data[$f], $label, $rParam]);
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
    private function required(mixed &$data, string $label): bool
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
    private function between(mixed &$data, string $label, string $param): bool
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
    private function number(mixed &$data, string $label, string $param): bool
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
    private function integer(mixed &$data, string $label, string $param): bool
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
    private function float(mixed &$data, string $label, string $param): bool
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
    private function boolean(mixed &$data, string $label, string $param): bool
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
    private function email(mixed &$data, string $label, string $param): bool
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
    private function array(mixed &$data, string $label, string $param): bool
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
    private function date(mixed &$data, string $label, string $param): bool
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
    private function alpha(mixed &$data, string $label, string $param): bool
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
    private function alphaNum(mixed &$data, string $label, string $param): bool
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
    private function alphaDash(mixed &$data, string $label, string $param): bool
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
    private function chs(mixed &$data, string $label, string $param): bool
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
    private function chsAlpha(mixed &$data, string $label, string $param): bool
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
    private function chsAlphaNum(mixed &$data, string $label, string $param): bool
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
    private function chsDash(mixed &$data, string $label, string $param): bool
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
    private function activeUrl(mixed &$data, string $label, string $param): bool
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
    private function url(mixed &$data, string $label, string $param): bool
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
    private function ip(mixed &$data, string $label, string $param): bool
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
    private function dateFormat(mixed &$data, string $label, string $param): bool
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
    private function in(mixed &$data, string $label, string $param): bool
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
    private function notIn(mixed &$data, string $label, string $param): bool
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
    private function notBetween(mixed &$data, string $label, string $param): bool
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
    private function length(mixed &$data, string $label, string $param): bool
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
    private function max(mixed &$data, string $label, string $param): bool
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
    private function min(mixed &$data, string $label, string $param): bool
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
    private function after(mixed &$data, string $label, string $param): bool
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
    private function before(mixed &$data, string $label, string $param): bool
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
    private function confirm(mixed &$data, string $label, string $param): bool
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
    private function different(mixed &$data, string $label, string $param): bool
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
    private function eq(mixed &$data, string $label, string $param): bool
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
    private function egt(mixed &$data, string $label, string $param): bool
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
    private function gt(mixed &$data, string $label, string $param): bool
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
    private function elt(mixed &$data, string $label, string $param): bool
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
    private function lt(mixed &$data, string $label, string $param): bool
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
    private function file(mixed &$data, string $label, string $param): bool
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
    private function image(mixed &$data, string $label, string $param): bool
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
    private function fileExt(mixed &$data, string $label, string $param): bool
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
    private function fileMime(mixed &$data, string $label, string $param): bool
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
    private function fileSize(mixed &$data, string $label, string $param): bool
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