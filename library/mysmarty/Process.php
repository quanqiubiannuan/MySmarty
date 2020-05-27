<?php

namespace library\mysmarty;

/**
 * 此类摘抄于https://www.php.net/manual/zh/function.exec.php
 * 仅支持linux系统
 */
class Process
{

    private $pid;

    private $command;

    /**
     * 构造方法
     *
     * @param string|bool $cl
     *            运行命令
     */
    public function __construct($cl = false)
    {
        if ($cl !== false) {
            $this->command = $cl;
            $pids = $this->getAllPid();
            if (!empty($pids)) {
                $this->setPid($pids[0]);
            }
        }
    }

    /**
     * 获取所有当前进程pid
     * @return array
     */
    public function getAllPid()
    {
        //查找pid
        $allProcess = self::getAllProcess();
        $pids = [];
        foreach ($allProcess as $v) {
            if ($v['cmd'] === $this->command) {
                $pids[] = $v['pid'];
            }
        }
        return $pids;
    }

    private function runCom()
    {
        $command = 'nohup ' . $this->command . ' > /dev/null 2>&1 & echo $!';
        exec($command, $op);
        $this->pid = (int)$op[0];
    }

    /**
     * 设置进程pid
     *
     * @param int $pid
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * 获取进程pid
     *
     * @return number|int
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * 查看运行状态
     *
     * @return boolean
     */
    public function status()
    {
        if (!empty($this->pid)) {
            return true;
        }
        return false;
    }

    /**
     * 运行
     *
     * @return boolean
     */
    public function start()
    {
        if ($this->command !== '') {
            $this->runCom();
        } else {
            return true;
        }
        return false;
    }

    /**
     * 停止
     *
     * @return boolean
     */
    public function stop()
    {
        $pids = $this->getAllPid();
        sort($pids);
        foreach ($pids as $pid) {
            self::kill($pid);
        }
        return true;
    }

    /**
     * 杀死进程
     *
     * @param int $pid
     *            进程id
     * @return boolean
     */
    public static function kill($pid)
    {
        $command = 'kill ' . $pid;
        exec($command, $output, $return_var);
        return true;
    }

    /**
     * 获取所有进程
     *
     * @return array|mixed[][]
     */
    public static function getAllProcess()
    {
        $data = [];
        exec('ps -ef', $output);
        if (!empty($output)) {
            foreach ($output as $k => $v) {
                if ($k > 0) {
                    $vArr = explode(' ', $v);
                    $tmp = [];
                    foreach ($vArr as $v2) {
                        if ($v2 == '') {
                            continue;
                        }
                        $tmp[] = $v2;
                    }

                    $tmp2 = [
                        'uid' => $tmp[0],
                        'pid' => $tmp[1],
                        'ppid' => $tmp[2],
                        'c' => $tmp[3],
                        'stime' => $tmp[4],
                        'tty' => $tmp[5],
                        'time' => $tmp[6],
                        'cmd' => implode(' ', array_slice($tmp, 7))
                    ];
                    $data[] = $tmp2;
                }
            }
            $data = array_reverse($data);
        }
        return $data;
    }
}