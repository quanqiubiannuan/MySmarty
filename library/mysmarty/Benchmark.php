<?php
/**
 * Date: 2019/5/6
 * Time: 17:02
 */

namespace library\mysmarty;

/**
 * 计算耗时时间与内存消耗
 * @package library\mysmarty
 */
class Benchmark
{

    private static $obj;

    private $data = [];

    private function __construct()
    {
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

    public function mark($point)
    {
        $this->data[$point] = [getCurrentMicroTime(), getMemoryUsage()];
    }

    /**
     * 获取执行时间，单位秒
     * @param string $startPoint
     * @param string $endPoint
     * @return int|mixed
     */
    public function elapsedTime($startPoint, $endPoint)
    {
        if (!isset($this->data[$startPoint], $this->data[$endPoint])) {
            return -1;
        }
        return $this->data[$endPoint][0] - $this->data[$startPoint][0];
    }

    /**
     * 获取执行内存，单位字节
     * 1字节(B) = 8 位(bit)
     * 1 kb = 1024 字节
     * 1 mb = 1024 kb
     * @param string $startPoint
     * @param string $endPoint
     * @return int|mixed
     */
    public function memoryUsage($startPoint, $endPoint)
    {
        if (!isset($this->data[$startPoint], $this->data[$endPoint])) {
            return -1;
        }
        return $this->data[$endPoint][1] - $this->data[$startPoint][1];
    }

    /**
     * 获取当前标记点的时间
     * @param string $point
     * @return int|mixed
     */
    public function getElapsedTime($point)
    {
        if (isset($this->data[$point])) {
            return $this->data[$point][0];
        }
        return -1;
    }

    /**
     * 获取所有点的当前时间
     * @return array
     */
    public function getAllElapsedTime()
    {
        $tmp = [];
        foreach ($this->data as $k => $v) {
            $tmp[$k] = $v[0];
        }
        return $tmp;
    }

    /**
     * 获取当前标记点的内存分配
     * @param string $point
     * @return int|mixed
     */
    public function getMemoryUsage($point)
    {
        if (isset($this->data[$point])) {
            return $this->data[$point][1];
        }
        return -1;
    }

    /**
     * 获取所有点的内存分配
     * @return array
     */
    public function getAllMemoryUsage()
    {
        $tmp = [];
        foreach ($this->data as $k => $v) {
            $tmp[$k] = $v[1];
        }
        return $tmp;
    }

    /**
     * 获取所有点的标记数据
     * @return array
     */
    public function getAllMarkData()
    {
        return $this->data;
    }
}