<?php

namespace library\mysmarty;
class Config
{
    /**
     * 设置配置
     */
    public static function initAllConfig(): void
    {
        $c1 = self::getDirConfigData(CONFIG_DIR);
        $c2 = self::getDirConfigData(APPLICATION_DIR . '/' . MODULE . '/config');
        $data = array_replace_recursive($c1, $c2);
        createDirByFile(CONFIG_FILE);
        file_put_contents(CONFIG_FILE, json_encode($data));
        define('CONFIG', $data);
    }

    /**
     * @param string $name 配置名称
     * @param mixed $defValue 默认值
     * @return mixed
     */
    public static function getConfig(string $name, mixed $defValue = ''): mixed
    {
        $config = CONFIG;
        if (preg_match('/[.]/', $name)) {
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
    private static function getDirConfigData(string $dir): array
    {
        $data = [];
        if (file_exists($dir)) {
            //读取$dir目录下的配置
            $files = scandir($dir);
            foreach ($files as $file) {
                if (str_ends_with($file, '.php')) {
                    $data[str_ireplace('.php', '', $file)] = require_once $dir . '/' . $file;
                }
            }
        }
        return $data;
    }
}