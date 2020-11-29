<?php

namespace library\mysmarty;

class ElasticsearchCache extends BaseCache
{

    public string $database;

    public string $table;

    public function __construct()
    {
        $this->database = config('mysmarty.caching_type_params.elasticsearch.database');
        $this->table = config('mysmarty.caching_type_params.elasticsearch.table');
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
        $result = ElasticSearch::getInstance()->setDataBase($this->database)
            ->setTable($this->table)
            ->setPk('cache_key')
            ->insert([
                'cache_key' => $cachekey,
                'content' => $content,
                'modified' => time() + $expire
            ]);
        return $result > 0;
    }

    /**
     * 清空所有缓存
     */
    public function purge(): bool
    {
        return ElasticSearch::getInstance()
                ->setDataBase($this->database)
                ->setTable($this->table)
                ->truncate() > 0;
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
        $data = ElasticSearch::getInstance()->setDataBase($this->database)
            ->setTable($this->table)
            ->setPk('cache_key')
            ->field('content,modified')
            ->where('cache_key', $cachekey)
            ->find();
        if (!empty($data)) {
            $content = $data['content'];
            $modified = $data['modified'];
            if ($modified >= time()) {
                return $content;
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
        return ElasticSearch::getInstance()
            ->setDataBase($this->database)
            ->setTable($this->table)
            ->setPk('cache_key')
            ->where('cache_key', $cachekey)
            ->del();
    }
}