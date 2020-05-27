<?php
/**
 * Date: 2019/5/21
 * Time: 16:05
 */

namespace library\mysmarty;

abstract class Middleware
{
    /**
     * 中间件执行方法
     * @return bool 返回 true 通过，false 不通过
     */
    abstract public function handle();

    /**
     * 失败执行方法
     * @return mixed
     */
    abstract public function fail();
}