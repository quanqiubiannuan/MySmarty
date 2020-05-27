<?php
/**
 * Date: 2019/4/16
 * Time: 10:34
 */

namespace application\home\jobs;


use library\mysmarty\MessageQueue;

class TestQueue extends MessageQueue
{

    /**
     * @param mixed $data 消息队列的数据
     */
    public function handle($data)
    {
        var_dump($data);
    }
}