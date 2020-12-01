<?php

namespace application\home\controller;

use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $this->assign('type', 1);
        $this->display();
    }
}