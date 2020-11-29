<?php

namespace library\mysmarty;

class MongodbCache extends BaseCache
{

    public string $database;

    public string $table;

    public function __construct()
    {
        $this->database = config('mysmarty.caching_type_params.mongodb.database');
        $this->table = config('mysmarty.caching_type_params.mongodb.table');
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
        Mongodb::getInstance()->name($this->database)
            ->table($this->table)
            ->delete([
                'cache_key' => $cachekey
            ]);
        return Mongodb::getInstance()->name($this->database)
                ->table($this->table)
                ->add([
                    'cache_key' => $cachekey,
                    'content' => utf8_encode($content),
                    'expire' => time() + $expire
                ]) > 0;
    }

    /**
     * 清空所有缓存
     */
    public function purge(): bool
    {
        return Mongodb::getInstance()->name($this->database)
            ->table($this->table)
            ->truncate();
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
        $result = Mongodb::getInstance()->name($this->database)
            ->table($this->table)
            ->where('cache_key', $cachekey)
            ->find();
        if (!empty($result)) {
            if ($result['expire'] >= time()) {
                return utf8_decode($result['content']);
            }
            $this->delete($cachekey);
        }
        return false;
    }

    /**
     * 删除缓存
     * @param string $cachekey key
     * @return bool
     */
    public function delete(string $cachekey): bool
    {
        return Mongodb::getInstance()->name($this->database)
            ->table($this->table)
            ->delete([
                'cache_key' => $cachekey
            ]);
    }
}