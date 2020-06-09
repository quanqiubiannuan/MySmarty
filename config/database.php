<?php
/**
 * 数据库配置
 */
return [
    /**
     * 不同的数据库可以配置不同的名称，mysql为默认连接名称
     */
    'mysql' => [
        // 主机ip
        'host' => '127.0.0.1',
        // mysql 用户名
        'user' => 'root',
        // mysql 密码
        'password' => '123456',
        // mysql 端口
        'port' => 3306,
        // mysql 默认数据库
        'database' => 'test',
        // mysql 字符编码
        'charset' => 'utf8'
    ],
    'redis' => [
        // 主机ip
        'host' => '127.0.0.1',
        // redis 端口
        'port' => 6379,
        // redis 密码
        'pass' => ''
    ],
    'elasticsearch' => [
        // 协议
        'protocol' => 'http',
        // 主机ip
        'ip' => '127.0.0.1',
        // 端口
        'port' => 9200,
        // 默认 数据库，索引
        'database' => 'test',
        // 默认 表，文档
        'table' => 'library'
    ],
    'memcached' => [
        // 主机ip
        'ip' => '127.0.0.1',
        // 端口
        'port' => 11211
    ],
    // 配置参考 https://www.php.net/manual/zh/mongodb-driver-manager.construct.php
    'mongodb' => [
        // mongodb连接uri
        'uri' => 'mongodb://127.0.0.1/',
        // 连接uri配置，会覆盖uri参数
        'uri_options' => [],
        // 驱动配置
        'driver_options' => [],
        // 默认数据库
        'database' => 'test',
        // 默认数据库表/集合
        'table' => 'library'
    ],
    'sqlite' => [
        //数据存放目录
        'database_dir' => ROOT_DIR . '/sqlite',
        //默认数据库
        'database' => 'test',
        //默认表
        'table' => 'library'
    ],
    'beanstalkd' => [
        // 主机ip
        'host' => '127.0.0.1',
        // beanstalkd 端口
        'port' => 11300
    ],
    'sphinxsearch' => [
        // 主机ip
        'host' => '127.0.0.1',
        // mysql 端口
        'port' => 9306,
        // mysql 字符编码
        'charset' => 'utf8mb4'
    ]
];