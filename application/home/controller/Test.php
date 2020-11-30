<?php

namespace application\home\controller;

use library\mysmarty\Controller;
use library\mysmarty\Route;

/**
 * Class Test
 * @package application\home\controller
 */
#[Route("/test", middleware: [\application\home\middleware\Test::class])]
class Test extends Controller
{
    #[Route("{name}/{id}")]
    public function test22($id, $name)
    {
        echo 'test';
    }
}