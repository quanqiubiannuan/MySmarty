<?php

namespace library\mysmarty;

use PDO;

/**
 * pdo连接池
 */
class MyPdo
{

    public static array $dbhs = [];

    public static string $database;

    /**
     * 获取pdo状态
     * @param string $key 键名
     * @return PDO|null
     */
    public static function getDbh(string $key): null|PDO
    {
        return self::$dbhs[$key] ?? null;
    }

    /**
     * 储存pdo对象
     * @param string $key 键名
     * @param PDO $dbh pdo对象
     */
    public static function setDbh(string $key, PDO $dbh)
    {
        self::$dbhs[$key] = $dbh;
    }
}