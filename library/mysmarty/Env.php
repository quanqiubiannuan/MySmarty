<?php

namespace library\mysmarty;

/**
 * 环境变量（动态配置）配置
 *
 * @author 戴记
 *
 */
class Env
{

    private static $file = ROOT_DIR . '/.env';

    /**
     * 读取
     *
     * @param string $key
     * @param string $defValue
     *            默认值
     * @return mixed
     */
    public static function get($key, $defValue = '')
    {
        if (file_exists(self::$file)) {
            $handle = fopen(self::$file, 'r');
            if ($handle) {
                while (($str = fgets($handle)) !== false) {
                    if (preg_match('/^' . $key . '=/i', $str)) {
                        $arr = explode('=', $str);
                        if (count($arr) === 2) {
                            return ($arr[1]);
                        }
                    }
                }
                fclose($handle);
            }
        }
        return $defValue;
    }
}