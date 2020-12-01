<?php

namespace application\home\controller;

use library\mysmarty\Cache;

class Index
{
    public function test()
    {
        //设置
        var_dump(Cache::set('name', '李先生',3000));
//        //获取
        var_dump(Cache::get('name'));
//        //删除
        var_dump(Cache::rm('name'));
        var_dump(Cache::get('name2', '默认值'));
    }
}