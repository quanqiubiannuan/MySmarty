<?php

namespace library\mysmarty;
/**
 * Memcached连接类
 * @author daiji
 *
 */
class Memcached
{

    // ip
    private $ip = CONFIG['database']['memcached']['ip'];

    // 端口
    private $port = CONFIG['database']['memcached']['port'];

    // 连接超时
    private $timeOut = 30;

    // 读取超时
    private $readTimeout = 3;

    // 存储值是否序列化
    private $isSerialize = true;

    private $handle;

    private static $obj;

    /**
     * Memcached constructor.
     * @throws
     */
    private function __construct()
    {
        $this->handle = fsockopen($this->ip, $this->port, $errno, $errstr, $this->timeOut);
        if ($errno !== 0) {
            throw new \RuntimeException('Memcached 连接失败');
        }
        if (is_resource($this->handle)) {
            stream_set_timeout($this->handle, $this->readTimeout);
        }
    }

    private function __clone()
    {
    }

    /**
     * 获取一个实例
     * @return $this
     * @throws
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }

    /**
     * 执行
     *
     * @param mixed ...$args
     */
    private function execCmd(...$args)
    {
        fwrite($this->handle, implode(' ', $args) . "\r\n");
    }

    /**
     * 读取一行
     *
     * @return string
     */
    private function readLine()
    {
        return trim(fgets($this->handle));
    }

    /**
     * 通用存储方法
     *
     * @param string $command
     *            命令
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    private function store($command, $key, $value, $expiration = 0, $flag = 0)
    {
        $value = preg_replace('/\\n/', "\r\n", $value);
        if ($this->isSerialize) {
            $value = serialize($value);
        }
        $this->execCmd($command, $key, $flag, $expiration, strlen($value));
        $this->execCmd($value);
        if ($this->readLine() === 'STORED') {
            return true;
        }
        return false;
    }

    /**
     * 存储一个元素
     *
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    public function set($key, $value, $expiration = 0, $flag = 0)
    {
        return $this->store('set', $key, $value, $expiration, $flag);
    }

    /**
     * 向一个新的key下面增加一个元素
     *
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    public function add($key, $value, $expiration = 0, $flag = 0)
    {
        return $this->store('add', $key, $value, $expiration, $flag);
    }

    /**
     * 替换已存在key下的元素
     *
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    public function replace($key, $value, $expiration = 0, $flag = 0)
    {
        return $this->store('replace', $key, $value, $expiration, $flag);
    }

    /**
     * 向已存在元素后追加数据
     *
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    public function append($key, $value, $expiration = 0, $flag = 0)
    {
        $srcValue = $this->get($key);
        if ($srcValue) {
            $value = $srcValue . $value;
        }
        return $this->store('set', $key, $value, $expiration, $flag);
    }

    /**
     * 向一个已存在的元素前面追加数据
     *
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    public function prepend($key, $value, $expiration = 0, $flag = 0)
    {
        $srcValue = $this->get($key);
        if ($srcValue) {
            $value .= $srcValue;
        }
        return $this->store('set', $key, $value, $expiration, $flag);
    }

    /**
     * 获取值
     *
     * @param string $key
     * @return boolean|string[]
     */
    private function obtain($key)
    {
        $this->execCmd('get', $key);
        $data = [];
        while (true) {
            $tmp = $this->readLine();
            if ($tmp === 'END') {
                break;
            }
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * 检索一个元素
     *
     * @param string $key
     * @param string $defValue
     * @return boolean
     */
    public function get($key, $defValue = '')
    {
        $data = $this->obtain($key);
        if ($data) {
            unset($data[0]);
            $result = implode("\r\n", $data);
            if ($this->isSerialize) {
                $result = unserialize($result);
            }
            return $result;
        }
        return $defValue;
    }

    /**
     * 获取带有 CAS 令牌存 的 value(数据值)
     *
     * @param string $key
     * @return boolean
     */
    public function gets($key)
    {
        $this->execCmd('gets', $key);
        $data = [];
        while (true) {
            $tmp = $this->readLine();
            if ($tmp === 'END') {
                break;
            }
            $data[] = $tmp;
        }
        if (!empty($data)) {
            $meta = explode(' ', $data[0]);
            return $meta[count($meta) - 1];
        }
        return '';
    }

    /**
     * 用于执行一个"检查并设置"的操作
     *
     * @param string $key
     *            用于存储值的键名
     * @param mixed $value
     *            存储的值
     * @param integer $expiration
     *            到期时间，默认为 0
     * @param integer $flag
     *            标志
     * @return boolean
     */
    public function cas($key, $value, $expiration = 0, $flag = 0)
    {
        $unique_cas_token = $this->gets($key);
        if (empty($unique_cas_token)) {
            return false;
        }
        $value = preg_replace('/\\n/', "\r\n", $value);
        $value = serialize($value);
        $this->execCmd('cas', $key, $flag, $expiration, strlen($value), $unique_cas_token);
        $this->execCmd($value);
        if ($this->readLine() === 'STORED') {
            return true;
        }
        return false;
    }

    /**
     * 删除一个元素
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key)
    {
        $this->execCmd('delete', $key);
        if ($this->readLine() === 'DELETED') {
            return true;
        }
        return false;
    }

    /**
     * 增加数值元素的值
     *
     * @param string $key
     *            要增加值的元素的key
     * @param integer $offset
     *            要将元素的值增加的大小
     * @return boolean|string
     */
    public function increment($key, $offset = 1)
    {
        if ($this->isSerialize) {
            $srcValue = $this->get($key);
            if (!$srcValue) {
                return false;
            }
            $newValue = (int)$srcValue + $offset;
            $result = $this->set($key, $newValue);
            return $result ? $newValue : false;
        }

        $this->execCmd('incr', $key, $offset);
        $result = $this->readLine();
        if (!is_numeric($result)) {
            return false;
        }
        return (int)$result;
    }

    /**
     * 减少数值元素的值
     *
     * @param string $key
     *            要减少值的元素的key
     * @param integer $offset
     *            要将元素的值减少的大小
     * @return boolean|string
     */
    public function decrement($key, $offset = 1)
    {
        if ($this->isSerialize) {
            $srcValue = $this->get($key);
            if (!$srcValue) {
                return false;
            }
            $newValue = (int)$srcValue - $offset;
            $result = $this->set($key, $newValue);
            return $result ? $newValue : false;
        }
        $this->execCmd('decr', $key, $offset);
        $result = $this->readLine();
        if (!is_numeric($result)) {
            return false;
        }
        return (int)$result;
    }

    /**
     * 返回统计信息
     *
     * @return mixed[][]
     */
    public function stats()
    {
        $this->execCmd('stats');
        $data = [];
        while (true) {
            $tmp = $this->readLine();
            if ($tmp === 'END') {
                break;
            }
            $tmpArr = explode(' ', $tmp);
            $data[$tmpArr[1]] = $tmpArr[2];
        }
        return $data;
    }

    /**
     * 显示各个 slab 中 item 的数目和存储时长
     *
     * @return string[]
     */
    public function statsItems()
    {
        $this->execCmd('stats items');
        $data = [];
        while (true) {
            $tmp = $this->readLine();
            if ($tmp === 'END') {
                break;
            }
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * 显示各个slab的信息，包括chunk的大小、数目、使用情况等
     *
     * @return string[]
     */
    public function statsSlabs()
    {
        $this->execCmd('stats slabs');
        $data = [];
        while (true) {
            $tmp = $this->readLine();
            if ($tmp === 'END') {
                break;
            }
            $data[] = $tmp;
        }
        return $data;
    }

    /**
     * 用于显示所有item的大小和个数
     *
     * @return array|mixed[]
     */
    public function statsSizes()
    {
        $this->execCmd('stats sizes');
        $data = [];
        while (true) {
            $tmp = $this->readLine();
            if ($tmp === 'END') {
                break;
            }
            $tmpArr = explode(' ', $tmp);
            $data = [
                'size' => $tmpArr[1],
                'num' => $tmpArr[2]
            ];
        }
        return $data;
    }

    /**
     * 作废缓存中的所有元素
     *
     * @param integer $delay
     *            在作废所有元素之前等待的时间（单位秒）
     * @return boolean
     */
    public function flush($delay = 0)
    {
        $this->execCmd('flush_all', $delay);
        if ($this->readLine() === 'OK') {
            return true;
        }
        return false;
    }

    /**
     * 设置连接超时时间
     * @param int $timeOut 单位，秒
     * @return $this
     */
    public function setTimeOut($timeOut)
    {
        $this->timeOut = $timeOut;
        return $this;
    }

    /**
     * 设置读取超时时间
     * @param int $readTimeout 单位，秒
     * @return $this
     */
    public function setReadTimeout($readTimeout)
    {
        $this->readTimeout = $readTimeout;
        return $this;
    }

    /**
     * 设置是否序列化
     * @param bool $isSerialize
     * @return $this
     */
    public function setIsSerialize($isSerialize)
    {
        $this->isSerialize = $isSerialize;
        return $this;
    }
}