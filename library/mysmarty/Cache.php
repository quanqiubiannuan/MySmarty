<?php

namespace library\mysmarty;
/**
 * 缓存类
 *
 * @author 戴记
 *
 */
class Cache
{

    /**
     * 添加缓存
     *
     * @param string $name
     *            键
     * @param string $value
     *            值
     * @param int $expire
     *            缓存时间，单位秒
     * @return number|integer
     */
    public static function set($name, $value, $expire = 3600)
    {
        $cachingType = config('smarty.caching_type');
        switch ($cachingType) {
            case 'mysql':
                $database = config('smarty.caching_type_params.mysql.database');
                $table = config('smarty.caching_type_params.mysql.table');
                return Model::getInstance()->name($database)
                    ->table($table)
                    ->add([
                        'id' => md5($name),
                        'name' => $name,
                        'content' => $value,
                        'modified' => date('Y-m-d H:i:s', time() + $expire)
                    ], true);
            case 'redis':
                Redis::getInstance()->select(config('smarty.caching_type_params.redis.db', 0));
                return Redis::getInstance()->set($name, $value, $expire);
            case 'elasticsearch':
                return ElasticSearch::getInstance()->setDataBase(config('smarty.caching_type_params.elasticsearch.database'))
                    ->setTable(config('smarty.caching_type_params.elasticsearch.table'))
                    ->insert([
                        'id' => md5($name),
                        'name' => $name,
                        'value' => $value,
                        'time' => time() + $expire
                    ]);
            case 'memcached':
                return Memcached::getInstance()->set($name, $value, $expire);
            case 'mongodb':
                self::rm($name);
                $database = config('smarty.caching_type_params.mongodb.database');
                $table = config('smarty.caching_type_params.mongodb.table');
                return Mongodb::getInstance()->name($database)
                    ->table($table)
                    ->add([
                        'name' => $name,
                        'value' => $value,
                        'expire' => time() + $expire
                    ]);
            default:
                $cacheDir = ROOT_DIR . '/runtime/cache';
                if (!file_exists($cacheDir)) {
                    if (!mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
                        return false;
                    }
                }
                return file_put_contents($cacheDir . '/' . md5($name), (time() + $expire) . '|' . $value);
        }
    }

    /**
     * 获取缓存
     *
     * @param string $name
     *            键
     * @param string $defValue
     *            默认值
     * @return string
     */
    public static function get($name, $defValue = '')
    {
        $cachingType = config('smarty.caching_type');
        switch ($cachingType) {
            case 'mysql':
                $database = config('smarty.caching_type_params.mysql.database');
                $table = config('smarty.caching_type_params.mysql.table');
                $data = Model::getInstance()->name($database)
                    ->table($table)
                    ->where('id', md5($name))
                    ->where('modified', date('Y-m-d H:i:s'), '>')
                    ->find();
                if (!empty($data)) {
                    return $data['content'];
                }
                return $defValue;
            case 'redis':
                Redis::getInstance()->select(config('smarty.caching_type_params.redis.db', 0));
                $value = Redis::getInstance()->get($name);
                if (!empty($value)) {
                    return $value;
                }
                return $defValue;
            case 'elasticsearch':
                $data = ElasticSearch::getInstance()->setDataBase(config('smarty.caching_type_params.elasticsearch.database'))
                    ->setTable(config('smarty.caching_type_params.elasticsearch.table'))
                    ->where('id', md5($name))
                    ->find();
                if (!empty($data)) {
                    if ($data['time'] >= time()) {
                        return $data['value'];
                    }
                }
                return $defValue;
            case 'memcached':
                return Memcached::getInstance()->get($name);
            case 'mongodb':
                $database = config('smarty.caching_type_params.mongodb.database');
                $table = config('smarty.caching_type_params.mongodb.table');
                $data = Mongodb::getInstance()->name($database)
                    ->table($table)
                    ->where('name', $name)
                    ->find();
                if (empty($data) || $data['expire'] < time()) {
                    return $defValue;
                }
                return $data['value'];
            default:
                $file = ROOT_DIR . '/runtime/cache/' . md5($name);
                if (!file_exists($file)) {
                    return $defValue;
                }
                $data = file_get_contents($file);
                $expireTime = substr($data, 0, strpos($data, '|'));
                if ($expireTime > time()) {
                    return substr($data, strpos($data, '|') + 1);
                }
                return $defValue;
        }
    }

    /**
     * 删除缓存
     *
     * @param string $name
     *            键
     * @return number|boolean
     */
    public static function rm($name)
    {
        $cachingType = config('smarty.caching_type');
        switch ($cachingType) {
            case 'mysql':
                $database = config('smarty.caching_type_params.mysql.database');
                $table = config('smarty.caching_type_params.mysql.table');
                return Model::getInstance()->name($database)
                    ->table($table)
                    ->where('id', md5($name))
                    ->delete();
            case 'redis':
                Redis::getInstance()->select(config('smarty.caching_type_params.redis.db', 0));
                return Redis::getInstance()->del($name);
            case 'elasticsearch':
                return ElasticSearch::getInstance()->setDataBase(config('smarty.caching_type_params.elasticsearch.database'))
                    ->setTable(config('smarty.caching_type_params.elasticsearch.table'))
                    ->where('id', md5($name))
                    ->del();
            case 'memcached':
                return Memcached::getInstance()->delete($name);
            case 'mongodb':
                $database = config('smarty.caching_type_params.mongodb.database');
                $table = config('smarty.caching_type_params.mongodb.table');
                return Mongodb::getInstance()->name($database)
                    ->table($table)
                    ->delete([
                        'name' => $name
                    ]);
            default:
                $file = ROOT_DIR . '/runtime/cache/' . md5($name);
                if (!file_exists($file)) {
                    return false;
                }
                return unlink($file);
        }
    }
}