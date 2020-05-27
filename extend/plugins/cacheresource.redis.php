<?php
use library\mysmarty\Redis;
/**
 * redis 缓存配置
 */
class Smarty_CacheResource_Redis extends Smarty_CacheResource_KeyValueStore
{
    private $db = 0;
    public function __construct()
    {
        $this->db = config('smarty.caching_type_params.redis.db', 0);
    }

    /**
     * 从redis中获取一系列key的值。
     *
     * @param array $keys
     *            多个key
     * @return array|boolean 成功返回按key的顺序返回的对应值，失败返回false
     */
    protected function read(array $keys)
    {
        $data = [];
        foreach ($keys as $v) {
            $data[$v] = Redis::getInstance()->setDb($this->db)->get($v);
        }
        if (empty($data)) {
            return false;
        }
        return $data;
    }

    /**
     * 将一系列的key对应的值存储到redis中。
     *
     * @param array $keys
     *            多个kv对应的数据值
     * @param int $expire
     *            过期时间
     * @return boolean 成功返回true，失败返回false
     */
    protected function write(array $keys, $expire = null)
    {
        if ($expire === null) {
            $expire = -1;
        }
        foreach ($keys as $k => $v) {
            Redis::getInstance()->setDb($this->db)->set($k, $v, $expire);
        }
        return true;
    }

    /**
     * 从redis中删除
     *
     * @param array $keys
     *            待删除的多个key
     * @return boolean 成功返回true，失败返回false
     */
    protected function delete(array $keys)
    {
        foreach ($keys as $k) {
            Redis::getInstance()->setDb($this->db)->del($k);
        }
        return true;
    }

    /**
     * 清空全部的值
     *
     * @return boolean 成功返回true，失败返回false
     */
    protected function purge()
    {
        Redis::getInstance()->setDb($this->db)->flushDb();
        return true;
    }
}