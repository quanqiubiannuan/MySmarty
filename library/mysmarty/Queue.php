<?php

namespace library\mysmarty;

/**
 * 基于redis实现的消息队列
 *
 * @author 戴记
 *
 */
class Queue
{

    private static $obj = null;

    //延迟队列名称
    private $delayQueueName = CONFIG['queue']['redis']['delay_queue_name'];

    //队列名称
    private $queueName = CONFIG['queue']['redis']['queue_name'];

    //库
    private $db = CONFIG['queue']['redis']['db'];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 获取队列实例
     *
     * @return Queue
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 添加消息
     * @param string $executeClassName 执行的类
     * @param mixed $data 数据
     * @param int $delay 延迟多少秒
     * @param bool $highPriority 是否优先执行
     * @return bool
     */
    public function add($executeClassName, $data, $delay = 0, $highPriority = false)
    {
        $queueData = [
            'executeClassName' => $executeClassName,
            'data' => $data
        ];
        if ($delay <= 0) {
            $queueData = serialize($queueData);
            if ($highPriority) {
                //头部加入
                return Redis::getInstance()->setDb($this->db)->lPush($this->queueName, $queueData);
            }

            return Redis::getInstance()->setDb($this->db)->rPush($this->queueName, $queueData);
        }

        $executeTime = time() + $delay;
        $queueData['executeTime'] = $executeTime;
        $queueData['highPriority'] = $highPriority;
        $queueData = serialize($queueData);
        //使用二分查找添加数据
        $len = Redis::getInstance()->setDb($this->db)->lLen($this->delayQueueName);
        if ($len < 1) {
            //直接添加
            Redis::getInstance()->setDb($this->db)->rPush($this->delayQueueName, $queueData);
        } else {
            //二分查找添加
            $start = 0;
            $end = $len - 1;
            $middle = 0;
            while (true) {
                $middle = (int)(($end + $start) / 2);
                if ($middle === $start || $middle === $end) {
                    break;
                }
                $middleData = Redis::getInstance()->setDb($this->db)->lIndex($this->delayQueueName, $middle);
                if (empty($middleData)) {
                    break;
                }
                $middleData = unserialize($middleData);
                if ($middleData['executeTime'] > $executeTime) {
                    $end = $middle;
                } else if ($middleData['executeTime'] < $executeTime) {
                    $start = $middle;
                } else {
                    break;
                }
            }
            $middleData = Redis::getInstance()->setDb($this->db)->lIndex($this->delayQueueName, $middle);
            $isBefore = false;
            $unMiddleData = unserialize($middleData);
            if ($unMiddleData['executeTime'] > $executeTime) {
                $isBefore = true;
            }
            return Redis::getInstance()->setDb($this->db)->lInsert($this->delayQueueName, $queueData, $middleData, $isBefore);
        }
        return false;
    }

    /**
     * 删除所有消息
     */
    public function clear()
    {
        Redis::getInstance()->setDb($this->db)->del($this->delayQueueName);
        Redis::getInstance()->setDb($this->db)->del($this->queueName);
    }

    /**
     * 删除延迟消息
     */
    public function clearDelayQueue()
    {
        Redis::getInstance()->setDb($this->db)->del($this->delayQueueName);
    }

    /**
     * 删除消息
     */
    public function clearQueue()
    {
        Redis::getInstance()->setDb($this->db)->del($this->queueName);
    }

    /**
     * 延迟消息推送到队列
     * @return int -1 没有数据
     */
    public function dispatchDelayQueue()
    {
        $len = Redis::getInstance()->setDb($this->db)->lLen($this->delayQueueName);
        if ($len < 1) {
            return -1;
        }
        //取出第一个元素
        $data = Redis::getInstance()->setDb($this->db)->lIndex($this->delayQueueName, 0);
        $unData = unserialize($data);
        if ($unData['executeTime'] <= time()) {
            //直接添加到执行队列中去
            $highPriority = $unData['highPriority'];
            unset($unData['highPriority'], $unData['executeTime']);
            if ($highPriority) {
                //头部加入
                Redis::getInstance()->setDb($this->db)->lPush($this->queueName, serialize($unData));
            } else {
                Redis::getInstance()->setDb($this->db)->rPush($this->queueName, serialize($unData));
            }
            Redis::getInstance()->setDb($this->db)->lPop($this->delayQueueName);
            return 0;
        }

        return (int)$unData['executeTime'] - time();
    }

    /**
     * 启动消息队列
     */
    public static function startQueue()
    {
        chdir(ROOT_DIR);
        exec('php mysmarty queue');
    }

    /**
     * 停止消息队列
     */
    public static function stopQueue()
    {
        chdir(ROOT_DIR);
        exec('php mysmarty queue stop');
    }

    /**
     * 消息队列装填
     * @return mixed
     */
    public static function statusQueue()
    {
        chdir(ROOT_DIR);
        exec('php mysmarty queue status', $output);
        return $output;
    }

    /**
     * 启动延迟消息队列
     */
    public static function startDelayQueue()
    {
        chdir(ROOT_DIR);
        exec('php mysmarty delay-queue');
    }

    /**
     * 停止延迟消息队列
     */
    public static function stopDelayQueue()
    {
        chdir(ROOT_DIR);
        exec('php mysmarty delay-queue stop');
    }

    /**
     * 延迟消息队列状态
     * @return mixed
     */
    public static function statusDelayQueue()
    {
        chdir(ROOT_DIR);
        exec('php mysmarty delay-queue status', $output);
        return $output;
    }

    /**
     * 消息队列处理
     */
    public static function goQueue()
    {
        global $argv;
        chdir(ROOT_DIR);
        $process = new Process('php mysmarty queue_background');
        $command = $argv[2] ?? '';
        switch ($command) {
            case 'stop':
                $process->stop();
                echoCliMsg('消息队列已关闭');
                break;
            case 'restart':
                $process->stop();
                $process->start();
                echoCliMsg('[已重启]消息队列后台运行中，运行进程PID：' . $process->getPid());
                break;
            case 'status':
                $status = $process->status();
                if ($status) {
                    $msg = '消息队列后台运行中，运行进程PID：' . implode(',', $process->getAllPid());
                } else {
                    $msg = '消息队列已关闭';
                }
                echoCliMsg($msg);
                break;
            default:
                $process->start();
                echoCliMsg('消息队列后台运行中，运行进程PID：' . $process->getPid());
        }
    }

    /**
     * debug模式
     */
    public static function goDebugQueue()
    {
        self::doQueue();
    }

    /**
     * 延迟消息队列运行
     */
    public static function goDelayQueue()
    {
        global $argv;
        chdir(ROOT_DIR);
        $process = new Process('php mysmarty delay_queue_background');
        $command = $argv[2] ?? '';
        switch ($command) {
            case 'stop':
                $process->stop();
                echoCliMsg('延迟消息队列已关闭');
                break;
            case 'restart':
                $process->stop();
                $process->start();
                echoCliMsg('[已重启]延迟消息队列后台运行中，运行进程PID：' . $process->getPid());
                break;
            case 'status':
                $status = $process->status();
                if ($status) {
                    $msg = '延迟消息队列后台运行中，运行进程PID：' . implode(',', $process->getAllPid());
                } else {
                    $msg = '延迟消息队列已关闭';
                }
                echoCliMsg($msg);
                break;
            default:
                $process->start();
                echoCliMsg('延迟消息队列后台运行中，运行进程PID：' . $process->getPid());
        }
    }

    /**
     * 调试模式
     */
    public static function goDebugDelayQueue()
    {
        self::doDelayQueue();
    }

    /**
     * 执行消息队列
     */
    public static function doQueue()
    {
        while (true) {
            $data = Redis::getInstance()->setDb(CONFIG['queue']['redis']['db'])->blPop(CONFIG['queue']['redis']['queue_name'], 0);
            if (empty($data) && !is_array($data)) {
                continue;
            }
            $data = unserialize($data[CONFIG['queue']['redis']['queue_name']]);
            $controller_namespace = $data['executeClassName'];
            $obj = new $controller_namespace();
            if (method_exists($obj, 'handle')) {
                $obj->do(serialize($data['data']));
            }
        }
    }

    /**
     * 延迟消息队列执行
     */
    public static function doDelayQueue()
    {
        while (true) {
            $result = self::getInstance()->dispatchDelayQueue();
            if ($result === -1) {
                $result = CONFIG['queue']['redis']['block_for'];
            } else {
                if ($result > CONFIG['queue']['redis']['block_for']) {
                    $result = CONFIG['queue']['redis']['block_for'];
                }
            }
            if ($result > 0) {
                sleep($result);
            }
        }
    }
}