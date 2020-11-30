<?php

namespace application\home\controller;

use library\mysmarty\Controller;
use library\mysmarty\Route;

class Index extends Controller
{
    #[Route('', level: Route::LOW)]
    public function test()
    {
        echo 'test';
    }
}