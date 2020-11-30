<?php

namespace application\home\middleware;

use library\mysmarty\Middleware;

class Test extends Middleware
{

    /**
     * 中间件执行方法
     * @param array $params 路由中的参数数组
     * @return bool 返回 true 通过，false 不通过
     */
    public function handle(array $params): bool
    {
        return true;
    }

    /**
     * 失败执行方法
     * @param array $params 路由中的参数数组
     */
    public function fail(array $params): void
    {
        error('失败啦');
    }
}