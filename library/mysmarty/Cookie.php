<?php

namespace library\mysmarty;

class Cookie
{

    private int $expire;
    private string $path;
    private string $domain;
    private bool $secure;
    private bool $httponly;
    private static ?self $obj = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (self::$obj === null) {
            self::$obj = new self();
            self::$obj->expire = config('session.lifetime', 3600);
            self::$obj->path = config('session.path', '/');
            self::$obj->domain = config('session.domain', '');
            self::$obj->secure = config('session.secure', false);
            self::$obj->httponly = config('session.httponly', true);
            if (empty(self::$obj->domain)) {
                self::$obj->domain = $_SERVER['SERVER_NAME'];
            }
        }
        return self::$obj;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @param int $expire
     */
    public function setExpire(int $expire): void
    {
        $this->expire = $expire;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain): void
    {
        $this->domain = $domain;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure(bool $secure): void
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function isHttponly(): bool
    {
        return $this->httponly;
    }

    /**
     * @param bool $httponly
     */
    public function setHttponly(bool $httponly): void
    {
        $this->httponly = $httponly;
    }

    /**
     * 设置
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function set(string $name, string $value): bool
    {
        return setcookie($name, $value, time() + intval(self::$obj->expire), self::$obj->path, self::$obj->domain, self::$obj->secure, self::$obj->httponly);
    }

    /**
     * 获取
     * @param string $name
     * @param string $defValue
     * @return string
     */
    public function get(string $name, string $defValue = ''): string
    {
        return $_COOKIE[$name] ?? $defValue;
    }

    /**
     * 删除
     * @param string $name
     * @return bool
     */
    public function delete(string $name): bool
    {
        if (isset($_COOKIE[$name])) {
            return setcookie($name, '', time() - 3600, self::$obj->path, self::$obj->domain, self::$obj->secure, self::$obj->httponly);
        }
        return false;
    }

    /**
     * 清空
     */
    public function clear(): void
    {
        if (count($_COOKIE) > 1) {
            foreach ($_COOKIE as $k => $v) {
                if ($k === 'PHPSESSID') {
                    continue;
                }
                setcookie($k, '', time() - 3600, self::$obj->path, self::$obj->domain, self::$obj->secure, self::$obj->httponly);
            }
        }
    }
}