<?php
/**
 * Date: 2019/5/21
 * Time: 16:04
 */

namespace application\home\middleware;


use library\mysmarty\Middleware;

class Test extends Middleware {

    /**
     * 中间件执行方法
     * @return bool 返回 true 通过，false 不通过
     */
    public function handle()
    {
        return false;
    }

    /**
     * 失败执行方法
     * @return mixed
     */
    public function fail()
    {
        error('失败啦');
    }
}