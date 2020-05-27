<?php

namespace library\mysmarty;

/**
 * 日志类
 *
 * @author 戴记
 *
 */
class Log
{

    /**
     * 写入日志
     *
     * @param string $info
     *            消息
     * @param string $type
     *            类型
     *            log 常规日志，用于记录日志
     *            error 错误，一般会导致程序的终止
     *            notice 警告，程序可以运行但是还不够完美的错误
     *            info 信息，程序输出信息
     *            debug 调试，用于调试信息
     *            sql SQL语句，用于SQL记录，只在数据库的调试模式开启时有效
     * @throws \Exception
     */
    public static function record($info, $type = 'record')
    {
        $dir = RUNTIME_DIR . '/logs';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $file = $dir . '/' . date('Ymd') . '.txt';
        $handle = fopen($file, 'a+');
        if (!$handle) {
            throw new \RuntimeException('log文件打开失败！');
        }
        $space = '';
        if (mb_strlen($type, 'utf-8') < 10) {
            $space .= str_repeat(' ', 10 - mb_strlen($type, 'utf-8'));
        }
        fwrite($handle, '[' . $type . ']' . $space . date('Y-m-d H:i:s') . str_repeat(' ', 10) . $info . PHP_EOL);
        fclose($handle);
    }

    /**
     * 常规日志，用于记录日志
     *
     * @param string $info
     * @throws \Exception
     */
    public static function log($info)
    {
        self::record($info, 'log');
    }

    /**
     * 错误，一般会导致程序的终止
     *
     * @param string $info
     * @throws \Exception
     */
    public static function error($info)
    {
        self::record($info, 'error');
    }

    /**
     * 警告，程序可以运行但是还不够完美的错误
     *
     * @param string $info
     * @throws \Exception
     */
    public static function notice($info)
    {
        self::record($info, 'notice');
    }

    /**
     * 信息，程序输出信息
     *
     * @param string $info
     * @throws \Exception
     */
    public static function info($info)
    {
        self::record($info, 'info');
    }

    /**
     * 调试，用于调试信息
     *
     * @param string $info
     * @throws \Exception
     */
    public static function debug($info)
    {
        self::record($info, 'debug');
    }

    /**
     * SQL语句，用于SQL记录
     *
     * @param string $sql
     * @throws \Exception
     */
    public static function sql($sql)
    {
        self::record($sql, 'sql');
    }

    /**
     * 清空日志
     */
    public static function clear()
    {
        $dir = RUNTIME_DIR . '/logs';
        if (!file_exists($dir)) {
            return;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            $file = $dir . '/' . $file;
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}