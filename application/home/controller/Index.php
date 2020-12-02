<?php

namespace application\home\controller;

use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $html = <<<'HTML'
xxxxxx
xxx
xxx
xxxx
xxx


xxx
HTML;

        $this->assign('html',$html);
        $this->display();
    }
}