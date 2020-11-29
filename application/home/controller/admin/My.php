<?php

namespace application\home\controller\admin;

use library\mysmarty\Route;
#[Route('my', level: Route::HIGN)]
class My
{
    #[Route('mytest', level: Route::HIGN)]
    public function home(int $a, string $age = '')
    {

    }
}