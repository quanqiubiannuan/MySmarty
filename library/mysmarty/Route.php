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
    // 高级别
    const HIGN = 9;
    // 中级别
    const MIDDLE = 5;
    // 低级别
    const LOW = 1;
    // 匹配级别，越大则越靠前
    private int $level;
    // 是否缓存
    private bool $caching;

    /**
     * 构造器.
     * @param string $url 路由地址，为空则使用默认路由
     * @param array $pattern 路由变量规则
     * @param array $middleware 路由中间件
     * @param int $level 路由匹配级别
     * @param bool $caching 是否缓存
     */
    public function __construct(string $url, array $pattern = [], array $middleware = [], int $level = self::MIDDLE, bool $caching = true)
    {
        $this->url = $url;
        $this->pattern = $pattern;
        $this->middleware = $middleware;
        $this->level = $level;
        $this->caching = $caching;
    }

    /**
     * 获取路由地址
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * 获取变量规则
     * @return array
     */
    public function getPattern(): array
    {
        return $this->pattern;
    }

    /**
     * 获取路由中间件规则
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * 获取路由级别
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * 是否缓存
     * @return bool
     */
    public function isCaching(): bool
    {
        return $this->caching;
    }
}