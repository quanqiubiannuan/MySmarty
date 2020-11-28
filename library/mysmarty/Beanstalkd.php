<?php
/**
 * Date: 2019/5/10
 * Time: 14:27
 */

namespace library\mysmarty;
class Beanstalkd
{
    private static $obj;

    // Beanstalkd ip
    private static $host = CONFIG['database']['beanstalkd']['host'];

    // Beanstalkd 端口
    private static $port = CONFIG['database']['beanstalkd']['port'];

    // Beanstalkd 连接超时时间，单位，秒
    private static $connectTimeOut = 5;

    // Beanstalkd 读取超时时间，单位，秒
    private static $readTimeOut = 3;

    private static $handle = null;

    private $error = '';

    private function __construct()
    {
        self::$handle = @fsockopen(self::$host, self::$port, $errno, $errstr, self::$connectTimeOut);
        if ($errno !== 0) {
            exit();
        }
        if (!isCliMode()) {
            stream_set_timeout(self::$handle, self::$readTimeOut);
        } else {
            stream_set_timeout(self::$handle, PHP_INT_MAX);
        }
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 关闭连接
     */
    public function __destruct()
    {
        if (is_resource(self::$handle)) {
            fclose(self::$handle);
        }
    }

    /**
     * 插入一个job到队列
     * @param string $body 插入的数据
     * @param int $priority 优先级，值越小优先级越高
     * @param int $delay 整型值，延迟ready的秒数
     * @param int $ttr 整型值，允许worker执行的最大秒数
     * @return bool|mixed
     */
    public function put($body, $priority = 1024, $delay = 0, $ttr = 30)
    {
        $bytes = strlen($body);
        $command = 'put ' . $priority . ' ' . $delay . ' ' . $ttr . ' ' . $bytes . "\r\n" . $body;
        if ($this->execCommand($command)) {
            $result = $this->getLine();
            switch (strtolower(substr($result, 0, 3))) {
                case 'bur':
                case 'ins':
                    return $this->getNumberByString($result);
                case 'exp':
                    $this->error = '$body必须以\r\n结尾';
                    return false;
                case 'job':
                    $this->error = '$body的长度超过max-job-size';
                    return false;
                case 'dra':
                    $this->error = '服务器资源耗尽';
                    return false;
            }
        }
        return false;
    }

    /**
     * 获取字符串中的数字
     * @param string $str
     * @return bool|mixed
     */
    private function getNumberByString($str)
    {
        if (preg_match('/([\d]+)/', $str, $mat)) {
            return $mat[1];
        }
        return false;
    }

    /**
     * 执行命令
     * @param string $command
     * @return bool|int
     */
    private function execCommand($command)
    {
        return fwrite(self::$handle, $command . "\r\n");
    }

    /**
     * 获取一行结果
     * @return bool|string
     */
    private function getLine()
    {
        return trim(fgets(self::$handle));
    }

    /**
     * 获取上一次错误信息
     * @return string
     */
    public function getLastError()
    {
        return $this->error;
    }

    /**
     * 切换管道
     * @param string $tube
     * @return $this
     * @throws
     */
    public function useTube($tube)
    {
        if ($this->execCommand('use ' . $tube)) {
            if (false !== stripos($this->getLine(), "USING")) {
                return $this;
            }
        }
        exit('使用管道失败');
    }

    /**
     *  取出（预订）job
     * @return array|bool
     */
    public function reserve()
    {
        if ($this->execCommand('reserve')) {
            return $this->reserveAfter();
        }
        return false;
    }

    /**
     * 取出（预订）job
     * 设置取job的超时时间，timeout设置为0时，服务器立即响应或者TIMED_OUT，积极的设置超时，将会限制客户端阻塞在取job的请求的时间。
     * @param int $timeout
     * @return array|bool
     */
    public function reserveWithTimeout($timeout)
    {
        if ($this->execCommand('reserve-with-timeout ' . $timeout)) {
            return $this->reserveAfter();
        }
        return false;
    }

    /**
     * 处理reserve后继结果
     * @return array|bool
     */
    private function reserveAfter()
    {
        $result = $this->getLine();
        switch (strtolower(substr($result, 0, 3))) {
            case 'res':
                $resultArr = explode(' ', $result);
                $data = fgets(self::$handle, $resultArr[2] + 1);
                $this->getLine();
                return [
                    'jobId' => $resultArr[1],
                    'data' => $data
                ];
            case 'dea':
                $this->error = '取出失败';
                return false;
            case 'tim':
                $this->error = '取出超时';
                return false;
        }
        return false;
    }

    /**
     * 从队列中删除一个job
     * @param int $jobId
     * @return bool
     */
    public function delete($jobId)
    {
        if ($this->execCommand('delete ' . $jobId)) {
            $result = $this->getLine();
            if (false !== stripos($result, 'DELETED')) {
                return true;
            }
        }
        $this->error = '未找到任务';
        return false;
    }

    /**
     * 将一个reserved的job放回ready queue
     * @param int $jobId
     * @param int $priority 优先级
     * @param int $delay 延迟时间
     * @return bool
     */
    public function release($jobId, $priority = 1024, $delay = 0)
    {
        if ($this->execCommand('release ' . $jobId . ' ' . $priority . ' ' . $delay)) {
            $result = $this->getLine();
            if (false !== stripos($result, 'RELEASED')) {
                return true;
            }
        }
        $this->error = '重置任务状态失败';
        return false;
    }

    /**
     * 将一个job的状态迁移为buried
     * @param int $jobId
     * @param int $priority 优先级
     * @return bool
     */
    public function bury($jobId, $priority = 1024)
    {
        if ($this->execCommand('bury ' . $jobId . ' ' . $priority)) {
            $result = $this->getLine();
            if (false !== stripos($result, 'BURIED')) {
                return true;
            } else {
                $this->error = '未找到任务';
            }
        }
        return false;
    }

    /**
     * 允许worker请求更多的时间执行job
     * @param int $jobId
     * @return bool
     */
    public function touch($jobId)
    {
        if ($this->execCommand('touch ' . $jobId)) {
            $result = $this->getLine();
            if (false !== stripos($result, 'TOUCHED')) {
                return true;
            } else {
                $this->error = '未找到任务';
            }
        }
        return false;
    }

    /**
     * 添加监控的tube到watch list列表
     * @param string $tube
     * @return bool|int 已监控的tube数量
     */
    public function watch($tube)
    {
        if ($this->execCommand('watch ' . $tube)) {
            $result = $this->getLine();
            if (false !== stripos($result, 'WATCHING')) {
                return $this->getNumberByString($result);
            }
        }
        return false;
    }

    /**
     * 从已监控的watch list列表中移出特定的tube 格式
     * @param string $tube
     * @return bool|int 已监控的tube数量
     */
    public function ignore($tube)
    {
        if ($this->execCommand('ignore ' . $tube)) {
            $result = $this->getLine();
            if (false !== stripos($result, 'WATCHING')) {
                return $this->getNumberByString($result);
            } else {
                $this->error = '移除错误';
            }
        }
        return false;
    }

    /**
     * 让client在系统中检查job，返回id对应的job
     * @param $jobId
     * @return bool|array
     */
    public function peekId($jobId)
    {
        $command = 'peek ' . $jobId;
        if ($this->execCommand($command)) {
            return $this->peekAfter();
        }
        return false;
    }

    /**
     * 执行peek
     * @return bool|array
     */
    private function peekAfter()
    {
        $result = $this->getLine();
        if (0 === stripos($result, 'FOUND')) {
            $resultArr = explode(' ', $result);
            $data = fgets(self::$handle, $resultArr[2] + 1);
            return [
                'jobId' => $resultArr[1],
                'data' => $data
            ];
        }
        $this->error = '没有相关执行任务';
        return false;
    }

    /**
     * 让client在系统中检查job,返回下一个ready job
     * @return bool|array
     */
    public function peekReady()
    {
        $command = 'peek-ready';
        if ($this->execCommand($command)) {
            return $this->peekAfter();
        }
        return false;
    }

    /**
     * 让client在系统中检查job,返回下一个延迟剩余时间最短的job
     * @return bool|array
     */
    public function peekDelayed()
    {
        $command = 'peek-delayed';
        if ($this->execCommand($command)) {
            return $this->peekAfter();
        }
        return false;
    }

    /**
     * 让client在系统中检查job,返回下一个在buried列表中的job
     * @return bool|array
     */
    public function peekBuried()
    {
        $command = 'peek-buried';
        if ($this->execCommand($command)) {
            return $this->peekAfter();
        }
        return false;
    }

    /**
     * 应用在当前使用的tube中，它将job的状态迁移为ready或者delayed 格式
     * @return bool|int
     */
    public function kick($bound)
    {
        $command = 'kick ' . $bound;
        if ($this->execCommand($command)) {
            return $this->getNumberByString($this->getLine());
        }
        return false;
    }

    /**
     * 可以使单个job被唤醒，使一个状态为buried或者delayed的job迁移为ready，所有的状态迁移都在相同的tube中完成 格式
     * @return bool
     */
    public function kickJob($jobId)
    {
        $command = 'kick-job ' . $jobId;
        if ($this->execCommand($command)) {
            if (false !== stripos($this->getLine(), 'KICKED')) {
                return true;
            }
        }
        return false;
    }

    /**
     * 统计job的相关信息
     * @param int $jobId
     * @return array|bool
     */
    public function statsJob($jobId)
    {
        if ($this->execCommand('stats-job ' . $jobId)) {
            return $this->deal();
        }
        return false;
    }

    /**
     * 统计tube的相关信息
     * @param string $tube
     * @return array|bool
     */
    public function statsTube($tube)
    {
        if ($this->execCommand('stats-tube ' . $tube)) {
            return $this->deal();
        }
        return false;
    }

    /**
     * 返回整个消息队列系统的整体信息
     * @return array|bool
     */
    public function stats()
    {
        if ($this->execCommand('stats')) {
            return $this->deal();
        }
        return false;
    }

    /**
     * 消息处理
     * @return array|bool
     */
    private function deal()
    {
        $result = $this->getLine();
        if (0 === stripos($result, 'OK')) {
            $len = $this->getNumberByString($result);
            $data = '';
            while (strlen($data) < $len) {
                $data .= fgets(self::$handle);
            }
            $this->getLine();
            $dataArr = explode(PHP_EOL, $data);
            $tmp = [];
            foreach ($dataArr as $v) {
                $vArr = explode(':', $v);
                if (count($vArr) === 2) {
                    $tmp[trim($vArr[0])] = trim($vArr[1]);
                }
            }
            return $tmp;
        }
        return false;
    }

    /**
     * 列表所有存在的tube 格式
     * @return array|bool
     */
    public function listTubes()
    {
        if ($this->execCommand('list-tubes')) {
            return $this->dealList();
        }
        return false;
    }

    /**
     * @return array|bool
     */
    private function dealList()
    {
        $result = $this->getLine();
        if (0 === stripos($result, 'OK')) {
            $len = $this->getNumberByString($result);
            $data = '';
            while (strlen($data) < $len) {
                $data .= fgets(self::$handle);
            }
            $this->getLine();
            $dataArr = explode(PHP_EOL, $data);
            $tmp = [];
            foreach ($dataArr as $v) {
                $vArr = explode(' ', $v);
                if (count($vArr) === 2) {
                    $tmp[] = trim($vArr[1]);
                }
            }
            return $tmp;
        }
        return false;
    }

    /**
     * 列表当前client正在use的tube 格式
     * @return string|bool
     */
    public function listTubeUsed()
    {
        if ($this->execCommand('list-tube-used')) {
            $result = $this->getLine();
            if (0 === stripos($result, 'USING')) {
                $resultArr = explode(' ', $result);
                return $resultArr[1];
            }
        }
        return false;
    }

    /**
     * 列表当前client watch的tube 格式
     * @return array|bool
     */
    public function listTubesWatched()
    {
        if ($this->execCommand('list-tubes-watched')) {
            return $this->dealList();
        }
        return false;
    }

    /**
     * 关闭连接 格式
     * @return bool
     */
    public function quit()
    {
        if ($this->execCommand('quit')) {
            return true;
        }
        return false;
    }

    /**
     * 此指令针对特定的tube内所有新的job延迟给定的秒数
     * @param string $tube
     * @param int $delay
     * @return bool
     */
    public function pauseTube($tube, $delay = 0)
    {
        if ($this->execCommand('pause-tube ' . $tube . ' ' . $delay) && false !== stripos($this->getLine(), 'PAUSED')) {
            return true;
        }
        return false;
    }
}