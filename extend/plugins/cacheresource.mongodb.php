<?php

use library\mysmarty\Mongodb;

/**
 * mongodb 缓存配置
 */
class Smarty_CacheResource_Mongodb extends Smarty_CacheResource_KeyValueStore
{

    private $database;

    private $table;

    public function __construct()
    {
        $this->database = config('smarty.caching_type_params.mongodb.database');
        $this->table = config('smarty.caching_type_params.mongodb.table');
    }

    /**
     * 从mongodb中获取一系列key的值。
     *
     * @param array $keys
     *            多个key
     * @return array|boolean 成功返回按key的顺序返回的对应值，失败返回false
     */
    protected function read(array $keys)
    {
        $data = [];
        foreach ($keys as $v) {
            $result = Mongodb::getInstance()->name($this->database)
                ->table($this->table)
                ->where('k', $v)
                ->find();
            if (!empty($result) && $result['expire'] >= time()) {
                $data[$v] = utf8_decode($result['v']);
            } else {
                $data[$v] = '';
            }
        }
        if (empty($data)) {
            return false;
        }
        return $data;
    }

    /**
     * 将一系列的key对应的值存储到mongodb中。
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
            Mongodb::getInstance()->name($this->database)
                ->table($this->table)
                ->delete([
                    'k' => $k
                ]);
            Mongodb::getInstance()->name($this->database)
                ->table($this->table)
                ->add([
                    'k' => $k,
                    'v' => utf8_encode($v),
                    'expire' => time() + $expire
                ]);
        }
        return true;
    }

    /**
     * 从mongodb中删除
     *
     * @param array $keys
     *            待删除的多个key
     * @return boolean 成功返回true，失败返回false
     */
    protected function delete(array $keys)
    {
        foreach ($keys as $k) {
            Mongodb::getInstance()->name($this->database)
                ->table($this->table)
                ->delete([
                    'k' => $k
                ]);
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
        return Mongodb::getInstance()->name($this->database)
            ->table($this->table)
            ->truncate();
    }
}