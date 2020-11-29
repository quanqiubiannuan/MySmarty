<?php

namespace application\home\controller;

use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $obj = new \ReflectionClass('application\home\controller\Test');
//        $methods = $obj->getMethods(\ReflectionMethod::IS_PUBLIC);
//        var_dump($methods);
//        $attributes = $obj->getAttributes();
        $attributes = $obj->getAttributes(Route::class);
        var_dump(count($attributes));
        var_dump($attributes[0]->getName());
        var_dump($attributes[0]->getArguments());
        var_dump($attributes[0]->newInstance());
    }
}