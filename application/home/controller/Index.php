<?php

namespace application\home\controller;

use library\mysmarty\Controller;
use library\mysmarty\Route;

class Index extends Controller
{
    public function test()
    {
        $obj = new \ReflectionClass('application\home\controller\Test');
        $attributes = $obj->getAttributes(Route::class);
        var_dump(count($attributes));
        var_dump($attributes[0]->getName());
        var_dump($attributes[0]->getArguments());
        var_dump($attributes[0]->newInstance());
    }
}