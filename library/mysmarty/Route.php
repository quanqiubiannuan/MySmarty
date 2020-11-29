<?php

namespace library\mysmarty;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Route
{
    // 路由规则
    private string $url;
    // 路由变量规则
    private array $pattern;
    // 路由中间件
    private array $middleware;

    public function __construct(string $url, array $pattern = [], array $middleware = [])
    {
        $this->url = $url;
        $this->pattern = $pattern;
        $this->middleware = $middleware;
    }
}