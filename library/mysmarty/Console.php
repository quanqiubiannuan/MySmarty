<?php

namespace library\mysmarty;

/**
 * Date: 2019/3/2
 * Time: 13:51
 */
class Console
{

    /**
     * 初始化
     */
    public static function start()
    {
        global $argv;
        if ($argv[0] !== 'mysmarty') {
            echoCliMsg('请使用 php mysmarty 命令');
        }
        if (empty($argv[1])) {
            echoCliMsg('php mysmarty 命令缺少参数');
        } else {
            switch ($argv[1]) {
                case 'queue':
                    Queue::goQueue();
                    break;
                case 'debug-queue':
                    Queue::goDebugQueue();
                    break;
                case 'delay-queue':
                    Queue::goDelayQueue();
                    break;
                case 'debug-delay-queue':
                    Queue::goDebugDelayQueue();
                    break;
                case 'queue_background':
                    Queue::doQueue();
                    break;
                case 'delay_queue_background':
                    Queue::doDelayQueue();
                    break;
                case 'run':
                    $s = 'localhost';
                    $p = 8080;
                    $len = count($argv);
                    for ($i = 2; $i < $len; $i++) {
                        switch (strtolower($argv[$i])) {
                            case '-p':
                                if (isset($argv[$i + 1])) {
                                    $p = $argv[$i + 1];
                                }
                                break 2;
                        }
                    }
                    echoCliMsg('PHP ' . PHP_VERSION . ' 开发服务器启动于 ' . date('Y-m-d H:i:s'));
                    echoCliMsg('运行在 ' . $s . ':' . $p);
                    echoCliMsg('按 CTRL-C 退出');
                    passthru('php -S ' . $s . ':' . $p . ' -t ' . ROOT_DIR . '/public/');
                    break;
                default:
                    //其它操作
                    $commandFile = ROOT_DIR . '/application/command.php';
                    if (!file_exists($commandFile)) {
                        echoCliMsg($commandFile . ' 文件不存在');
                        exit();
                    }
                    $command = requireReturnFile($commandFile);
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
                    $do = $argv[2] ?? '';
                    $tmp = '';
                    for ($i = 3; $i < $len; $i++) {
                        if ($i % 2 !== 0) {
                            // 键
                            $tmp = $commandArr[$i];
                        } else {
                            // 值
                            $_GET[$tmp] = $commandArr[$i];
                        }
                    }
                    if (empty($do)) {
                        Start::go(formatModule($commandArr[0]), formatController($commandArr[1]), formatAction($commandArr[2]), $_GET);
                    } else {
                        if (isWin()) {
                            echoCliMsg('Win电脑不支持此操作！');
                            exit();
                        }
                        chdir(ROOT_DIR);
                        $process = new Process('php mysmarty ' . $command);
                        switch ($do) {
                            case 'start':
                                $process->start();
                                echoCliMsg($command . ' 后台运行中：运行进程PID：' . $process->getPid());
                                break;
                            case 'stop':
                                $process->stop();
                                echoCliMsg($command . ' 已停止运行');
                                break;
                            case 'restart':
                                $process->stop();
                                $process->start();
                                echoCliMsg('[已重启]' . $command . '，运行进程PID：' . $process->getPid());
                                break;
                            case 'status':
                                $status = $process->status();
                                if ($status) {
                                    $msg = $command . ' 后台运行中，运行进程PID：' . implode(',', $process->getAllPid());
                                } else {
                                    $msg = $command . ' 已关闭';
                                }
                                echoCliMsg($msg);
                                break;
                            default:
                                Start::go(formatModule($commandArr[0]), formatController($commandArr[1]), formatAction($commandArr[2]), $_GET);
                        }
                    }
            }
        }
    }
}