<?php
// MySmarty模板配置
return [
    // 检查模板文件是否修改过，线上环境最好设置为false
    'compile_check' => true,
    // 强制编译，线上环境最好设置为false。开启缓存时，必须设置为false
    'force_compile' => true,
    // 缓存开启，0 关闭，1 开启
    'cache' => 0,
    /**
     * 自定义缓存存储方式
     * 为空，使用文件缓存
     * mysql，使用mysql作为缓存存放位置
     * redis，使用redis作为缓存存放位置
     * elasticsearch，使用elasticsearch作为缓存存放位置
     * memcached，使用memcached作为缓存存放位置
     * mongodb，使用mongodb作为缓存存放位置
     */
    'caching_type' => '',
    // 自定义缓存配置参数
    'caching_type_params' => [
        // mysql缓存配置
        'mysql' => [
            // 数据库
            'database' => 'test',
            // 缓存表
            'table' => 'cache'
        ],
        // redis缓存配置
        'redis' => [
            // 使用第几个库（0 - 15）
            'db' => 0
        ],
        // elasticsearch 缓存配置
        'elasticsearch' => [
            // 默认 数据库，索引
            'database' => 'mycache',
            // 默认 表，文档
            'table' => 'cache'
        ],
        'mongodb' => [
            // 默认 数据库，索引
            'database' => 'mycache',
            // 默认 表，文档
            'table' => 'cache'
        ]
    ],
    // 缓存时间,单位秒
    'cache_life_time' => 3600,
    // 输出过滤器,格式化页面，将源代码输出到一行，节省页面大小，false 不格式化，true 格式化（开启后，代码中尽量不要有注释符号，js,css代码要规范，用分号 `;` 隔开每一行）
    'load_output_filter' => false,
    // 标签库标签开始标签,为空则默认为 {
    'taglib_begin' => '',
    // 标签库标签结束标记,为空则默认为 }
    'taglib_end' => '',
    // 模板文件后缀
    'suffix' => 'html'
];