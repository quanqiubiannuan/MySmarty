<?php
return [
    // smtp发送电子邮件
    'smtp' => [
        // 发送服务器
        'hostname' => '',
        // 端口
        'port' => 465,
        // 是否使用SSL
        'useSSl' => true,
        // 发送邮箱
        'sendEmailUser' => '',
        // 发送邮箱密码/授权码
        'sendEmailPass' => '',
        // 发送邮箱显示名称
        'showEmail' => '',
        // 连接超时，单位秒
        'timeout' => 5,
        // 读取超时，单位秒
        'readTimeout' => 3
    ]
];