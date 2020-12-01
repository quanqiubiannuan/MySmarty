<?php

namespace library\mysmarty;

/**
 * 数据库便捷查询类
 *

 *
 */
class Db
{

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 查询
     *
     * @param string $sql
     * @param array $bindArgs
     * @return array
     */
    public static function query($sql, $bindArgs = [])
    {
        return Model::getInstance()->query($sql, $bindArgs);
    }

    /**
     * 添加、更新、删除
     *
     * @param string $sql
     * @param array $bindArgs
     * @return number
     */
    public static function execute($sql, $bindArgs = [])
    {
        return Model::getInstance()->execute($sql, $bindArgs);
    }

    /**
     * 连接其它数据库
     *
     * @param string $config
     *            配置中的名字，如 mysql
     * @return Model
     */
    public static function connect($config)
    {
        return Model::getInstance($config);
    }
}