<?php

namespace library\mysmarty;

/**
 * 数据库便捷查询类
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
     * @param string $sql sql语句
     * @param array $bindArgs 绑定的参数
     * @return array
     */
    public static function query(string $sql, array $bindArgs = []): array
    {
        return Model::getInstance()->query($sql, $bindArgs);
    }

    /**
     * 添加、更新、删除
     * @param string $sql
     * @param array $bindArgs
     * @return int
     */
    public static function execute(string $sql, array $bindArgs = []): int
    {
        return Model::getInstance()->execute($sql, $bindArgs);
    }

    /**
     * 连接其它数据库
     * @param string $config 配置中的名字，如 mysql
     * @return Model
     */
    public static function connect(string $config): Model
    {
        return Model::getInstance($config);
    }
}