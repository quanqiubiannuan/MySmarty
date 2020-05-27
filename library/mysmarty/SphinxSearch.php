<?php

namespace library\mysmarty;

use Exception;
use PDO;
use RuntimeException;

/**
 * Sphinx搜索
 * @package library\mysmarty
 */
class SphinxSearch
{
    private static $obj;
    private $dbh;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 获取Sphinx搜索实例
     * @return SphinxSearch
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
            self::$obj->connect();
        }
        return self::$obj;
    }

    /**
     * 获得一个连接
     */
    private function connect()
    {
        try {
            $host = config('database.sphinxsearch.host');
            $port = config('database.sphinxsearch.port');
            $charset = config('database.sphinxsearch.charset');
            $this->dbh = new PDO('mysql:host=' . $host . ';port=' . $port . ';charset=' . $charset, '', '', array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ));
        } catch (Exception $e) {
            throw new RuntimeException('sphinxsearch 连接失败');
        }
    }

    /**
     * 查询数据
     * @param string $sql 数据库语句
     * @param array $mWhereArgs 绑定的参数
     * @return array
     */
    public function query($sql, $mWhereArgs = [])
    {
        $data = [];
        $result = $this->dbh->prepare($sql);
        $res = $result->execute($mWhereArgs);
        if (!$res) {
            return $data;
        }
        if ($result) {
            while (true) {
                $row = $result->fetch(PDO::FETCH_ASSOC);
                if (!$row) {
                    break;
                }
                $data[] = $row;
            }
        }
        return $data;
    }

    /**
     *
     * @param string $sql
     *            原生sql语句
     * @param array $mWhereArgs
     *            绑定的参数
     * @return number
     * @throws
     */
    public function execute($sql, $mWhereArgs = [])
    {
        $isInsert = false;
        if (0 === stripos($sql, 'insert')) {
            $isInsert = true;
        }
        $result = $this->dbh->prepare($sql);
        $res = $result->execute($mWhereArgs);
        if (!$res) {
            return 0;
        }
        if (empty($result)) {
            return 0;
        }
        if ($isInsert) {
            $num = $this->dbh->lastInsertId();
            if (empty($num) && !empty($result->rowCount())) {
                $num = $result->rowCount();
            }
        } else {
            $num = $result->rowCount();
        }
        return (int)$num;
    }
}