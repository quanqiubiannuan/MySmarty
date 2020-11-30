<?php

namespace library\mysmarty;

abstract class Middleware
{
    /**
     * 中间件执行方法
     * @param array $params 路由中的参数，关联数组
     * @return bool 返回 true 通过，false 不通过
     */
    abstract public function handle(array $params): bool;

    /**
     * 失败执行方法
     * @param array $params 路由中的参数，关联数组
     */
    abstract public function fail(array $params): void;
}