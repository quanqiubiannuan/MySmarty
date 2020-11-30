<?php

namespace library\mysmarty;
class Console
{

    /**
     * 初始化
     */
    public static function start(): void
    {
        global $argv;
        if ($argv[0] !== 'mysmarty') {
            echoCliMsg('请使用 php mysmarty 命令');
        }
        if (empty($argv[1])) {
            echoCliMsg('php mysmarty 命令缺少参数');
        } else {
            $commandFile = ROOT_DIR . '/application/command.php';
            if (!file_exists($commandFile)) {
                echoCliMsg($commandFile . ' 文件不存在');
                exit();
            }
            $command = require_once $commandFile;
            $c = $argv[1];
            if (!isset($command[$c])) {
                if (preg_match('/\//', $c)) {
                    $command = trim($c, '/');
                } else {
                    echoCliMsg($c . ' 命令不存在');
                    exit();
                }
            } else {
                $command = trim($command[$c], '/');
            }
            $commandArr = explode('/', $command);
            $len = count($commandArr);
            if ($len < 3) {
                echoCliMsg($c . ' 命令错误');
                exit();
            }
            $module = array_shift($commandArr);
            $action = array_pop($commandArr);
            $controller = implode('\\', $commandArr);
            // 获取参数
            $params = [];
            $len = count($argv);
            for ($i = 2; $i < $len; $i++) {
                $params[] = $argv[$i];
            }
            Start::$module = formatModule($module);
            Start::$controller = formatController($controller);
            Start::$action = formatAction($action);
            Start::go(params: $params);
        }
    }
}