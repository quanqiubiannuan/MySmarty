<?php
// redis消息队列设置
return [
    'redis' => [
        //使用的redis库
        'db' => 0,
        //延迟队列等待执行的阻塞时间，单位秒
        'block_for' => 60,
        //队列名称
        'queue_name' => 'message_queue_',
        //延迟队列名称
        'delay_queue_name' => 'message_queue_delay_'
    ],
];