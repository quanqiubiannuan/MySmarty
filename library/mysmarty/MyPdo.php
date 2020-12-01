<?php

namespace library\mysmarty;

/**
 * pdo连接池
 *

 *
 */
class MyPdo
{

    public static $dbhs = [];

    public static $database;

    /**
     * 获取pdo状态
     *
     * @param string $key
     *            键名
     * @return mixed|NULL
     */
    public static function getDbh($key)
    {
        return self::$dbhs[$key] ?? null;
    }

    /**
     * 储存pdo对象
     *
     * @param string $key
     *            键名
     * @param object $dbh
     *            pdo对象
     */
    public static function setDbh($key, $dbh)
    {
        self::$dbhs[$key] = $dbh;
    }
}