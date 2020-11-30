<?php

namespace library\mysmarty\cache;

use library\mysmarty\Redis;

class RedisCache extends BaseCache
{
    public int $db = 0;

    public function __construct()
    {
        $this->db = config('mysmarty.caching_type_params.redis.db', 0);
    }

    /**
     * 读取缓存
     * @param string $cachekey key
     * @return string|bool
     */
    public function read(string $cachekey): string|bool
    {
        return Redis::getInstance()->setDb($this->db)->get($cachekey);
    }

    /**
     * 写入缓存
     * @param string $cachekey key
     * @param string $content 内容
     * @param int $expire 过期时间，单位：秒
     * @return bool
     */
    public function write(string $cachekey, string $content, int $expire = 3600): bool
    {
        return Redis::getInstance()->setDb($this->db)->set($cachekey, $content, $expire);
    }

    /**
     * 删除缓存
     * @param string $cachekey key
     * @return bool
     */
    public function delete(string $cachekey): bool
    {
        return Redis::getInstance()->setDb($this->db)->del($cachekey);
    }

    /**
     * 清空所有缓存
     */
    public function purge(): bool
    {
        return Redis::getInstance()->setDb($this->db)->flushDb();
    }

    /**
     * 检查是否有缓存
     * @param string $cachekey 缓存key
     * @return bool
     */
    public function isCached(string $cachekey): bool
    {
        return Redis::getInstance()->setDb($this->db)->exists($cachekey);
    }
}