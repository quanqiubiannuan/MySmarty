<?php

use library\mysmarty\Memcached;

/**
 * Memcached 缓存配置
 */
class Smarty_CacheResource_Memcached extends Smarty_CacheResource_KeyValueStore
{

    public function __construct()
    {
    }

    /**
     * 从Memcached中获取一系列key的值。
     *
     * @param array $keys
     *            多个key
     * @return array|bool 成功返回按key的顺序返回的对应值，失败返回false
     */
    protected function read(array $keys)
    {
        $data = [];
        foreach ($keys as $v) {
            $data[$v] = Memcached::getInstance()->setIsSerialize(false)->get($v);
        }
        if (empty($data)) {
            return false;
        }
        return $data;
    }

    /**
     * 将一系列的key对应的值存储到Memcached中。
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
            $expire = 0;
        }
        foreach ($keys as $k => $v) {
            Memcached::getInstance()->setIsSerialize(false)->set($k, $v, $expire);
        }
        return true;
    }

    /**
     * 从Memcached中删除
     *
     * @param array $keys
     *            待删除的多个key
     * @return boolean 成功返回true，失败返回false
     */
    protected function delete(array $keys)
    {
        foreach ($keys as $k) {
            Memcached::getInstance()->delete($k);
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
        Memcached::getInstance()->flush();
        return true;
    }
}