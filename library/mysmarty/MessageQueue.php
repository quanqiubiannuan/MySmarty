<?php
/**
 * Date: 2019/4/16
 * Time: 10:35
 */

namespace library\mysmarty;

abstract class MessageQueue{
    /**
     * @param mixed $data 消息队列的数据
     */
    abstract public function handle($data);

    /**
     * 执行消息队列
     * @param string $data
     */
    public function do($data){
        $this->handle(unserialize($data));
    }
}