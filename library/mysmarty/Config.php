<?php

namespace library\mysmarty;

class Config
{
    /**
     * 设置配置
     */
    public static function initAllConfig()
    {
        if (!defined('CONFIG')) {
            $c1 = self::getDirConfigData(CONFIG_DIR);
            $c2 = [];
            if (defined('MODULE')){
                $c2 = self::getDirConfigData(APPLICATION_DIR . '/' . MODULE . '/config');
            }
            define('CONFIG', array_replace_recursive($c1, $c2));
        }
    }

    /**
     * @param string $name 配置名称
     * @param string $defValue 默认值
     * @return array|string
     */
    public static function getConfig($name, $defValue = '')
    {
        $config = CONFIG;
        if (preg_match('/[\.]/', $name)) {
            $arr = explode('.', $name);
            foreach ($arr as $v) {
                if (isset($config[$v])) {
                    $config = $config[$v];
                } else {
                    return $defValue;
                }
            }
            return $config;
        }
        return $config[$name] ?? $defValue;
    }

    /**
     * 读取指定文件夹下的配置文件
     * @param string $dir
     * @return array
     */
    private static function getDirConfigData($dir)
    {
        $data = [];
        if (file_exists($dir)) {
            //读取$dir目录下的配置
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && preg_match('/\.php$/i', $file)) {
                    $data[str_ireplace('.php', '', $file)] = getRequireOnceData($dir . '/' . $file);
                }
            }
        }
        return $data;
    }
}