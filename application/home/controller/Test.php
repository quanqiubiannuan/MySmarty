<?php

namespace application\home\controller;

use library\mysmarty\Controller;

/**
 * Class Test
 * @package application\home\controller
 */
#[Route("/api/posts/{id}")]
class Test extends Controller
{
    #[Route("/api/posts/{id}")]
    public function test()
    {
        echo 'test';
    }

    public function test2(){
        $this->test();
    }
}