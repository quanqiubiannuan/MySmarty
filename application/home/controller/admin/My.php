<?php

namespace application\home\controller\admin;

use library\mysmarty\Controller;
use library\mysmarty\Route;

class My extends Controller
{
    #[Route('/test')]
    public function testMy()
    {
        $this->display();
    }
}