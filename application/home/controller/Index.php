<?php

namespace application\home\controller;


use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        getCacheKey();
        exit();
        $this->display();
    }
}