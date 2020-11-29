<?php

namespace application\home\controller;

use library\mysmarty\Controller;
use library\mysmarty\Route;

/**
 * Class Test
 * @package application\home\controller
 */
#[Route("/api/posts/{id}", pattern: [
    'id' => '[\d]+'
],middleware: [
    \application\home\middleware\Test::class => [
        'except' => ['test','test2'],
        'only' => ['test3','test4'],
    ]
])]
class Test extends Controller
{
    #[Route("/api/posts/{id}")]
    public function test()
    {
        echo 'test';
    }

    public function test2()
    {
        $this->test();
    }
}