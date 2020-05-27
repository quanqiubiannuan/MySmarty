<?php

namespace library\mysmarty;

class MemcachedCache extends BaseCache
{

    /**
     * 写入缓存
     * @param string $cachekey key
     * @param string $content 内容
     * @param int $expire 过期时间，单位：秒
     * @return bool
     */
    public function write(string $cachekey, string $content, int $expire = 3600): bool
    {
        return Memcached::getInstance()->set($cachekey, $content, $expire);
    }

    /**
     * 删除缓存
     * @param string $cachekey key
     * @return bool
     */
    public function delete(string $cachekey): bool
    {
        return Memcached::getInstance()->delete($cachekey);
    }

    /**
     * 清空所有缓存
     */
    public function purge(): bool
    {
        return Memcached::getInstance()->flush();
    }

    /**
     * 检查是否有缓存
     * @param string $cachekey 缓存key
     * @return bool
     */
    public function isCached(string $cachekey): bool
    {
        $data = $this->read($cachekey);
        if (!empty($data)) {
            return true;
        }
        return false;
    }

    /**
     * 读取缓存
     * @param string $cachekey key
     * @return mixed
     */
    public function read(string $cachekey)
    {
        return Memcached::getInstance()->get($cachekey, false);
    }
}