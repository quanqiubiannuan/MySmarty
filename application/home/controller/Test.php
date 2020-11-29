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
], middleware: [
    \application\home\middleware\Test::class => [
        'only' => ['test2', 'test'],
    ]
])]
class Test extends Controller
{
    #[Route("{name}")]
    public function test22()
    {
        echo 'test';
    }

    #[Route('test2', middleware: [
        \application\home\middleware\Test2::class,
    ])]
    public function test2()
    {
        $this->test();
    }

    public function TestPhp()
    {

    }
}