<?php

namespace library\mysmarty;

class MysqlCache extends BaseCache
{
    // 数据库
    public string $database;

    // 表名
    public string $table;

    public function __construct()
    {
        $this->database = config('view.caching_type_params.mysql.database');
        $this->table = config('view.caching_type_params.mysql.table');
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
        return Model::getInstance()->name($this->database)
                ->table($this->table)
                ->add([
                    'cache_key' => $cachekey,
                    'content' => $content,
                    'modified' => date('Y-m-d H:i:s', time() + $expire)
                ], true) > 0;
    }

    /**
     * 清空所有缓存
     */
    public function purge(): bool
    {
        return Model::getInstance()->name($this->database)
                ->table($this->table)
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
        $data = Model::getInstance()->name($this->database)
            ->table($this->table)
            ->field('content,modified')
            ->where('cache_key', $cachekey)
            ->find();
        if (!empty($data)) {
            if (strtotime($data['modified']) >= time()) {
                return $data['content'];
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
        return Model::getInstance()->name($this->database)
                ->table($this->table)
                ->where('cache_key', $cachekey)
                ->lt('modified', date('Y-m-d H:i:s'))
                ->delete() > 0;
    }
}