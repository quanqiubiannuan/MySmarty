<?php

namespace library\mysmarty;
/**
 * MySmarty缓存类
 * @package library\mysmarty
 */
abstract class BaseCache
{
    /**
     * 读取缓存
     * @param string $cachekey 缓存key
     * @return mixed
     */
    abstract public function read(string $cachekey);

    /**
     * 写入缓存
     * @param string $cachekey 缓存key
     * @param string $content 内容
     * @param int $expire 过期时间，单位秒
     * @return bool
     */
    abstract public function write(string $cachekey, string $content, int $expire = 3600): bool;

    /**
     * 删除缓存
     * @param string $cachekey 缓存key
     * @return bool
     */
    abstract public function delete(string $cachekey): bool;

    /**
     * 清空全部缓存
     * @return bool
     */
    abstract public function purge(): bool;

    /**
     * 是否存在缓存
     * @param string $cachekey 缓存key
     * @return bool
     */
    abstract public function isCached(string $cachekey): bool;
}