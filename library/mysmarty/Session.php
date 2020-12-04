<?php

namespace library\mysmarty;

/**
 * session操作类
 */
class Session
{
    private int $lifetime = 0;
    private string $path = '';
    private string $domain = '';
    private bool $secure = false;
    private bool $httponly = false;
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
        }
        return self::$obj;
    }

    /**
     * @return int
     */
    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return bool
     */
    public function getSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function getHttponly(): bool
    {
        return $this->httponly;
    }

    /**
     * Cookie 的 生命周期，以秒为单位。
     * @param int $lifetime
     * @return static
     */
    public function setLifetime(int $lifetime): static
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * 此 cookie 的有效 路径。 on the domain where 设置为“/”表示对于本域上所有的路径此 cookie 都可用。
     * @param string $path
     * @return static
     */
    public function setPath(string $path): static
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Cookie 的作用 域。 例如：“www.php.net”。 如果要让 cookie 在所有的子域中都可用，此参数必须以点（.）开头，例如：“.php.net”。
     * @param string $domain
     * @return static
     */
    public function setDomain(string $domain): static
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 设置为 TRUE 表示 cookie 仅在使用 安全 链接时可用。
     * @param bool $secure
     * @return static
     */
    public function setSecure(bool $secure): static
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * 设置为 TRUE 表示 PHP 发送 cookie 的时候会使用 httponly 标记。
     * @param bool $httponly
     * @return static
     */
    public function setHttponly(bool $httponly): static
    {
        $this->httponly = $httponly;
        return $this;
    }

    /**
     * 开启session
     */
    public function startSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            $lifetime = $this->lifetime ?: config('session.lifetime', 3600);
            $path = $this->path ?: config('session.path', '/');
            $domain = $this->domain ?: config('session.domain', '');
            $secure = $this->secure ?: config('session.secure', false);
            $httponly = $this->httponly ?: config('session.httponly', true);
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
            session_start();
        }
    }

    /**
     * 设置
     * @param string $name
     * @param mixed $value
     */
    public function set(string $name, mixed $value): void
    {
        $this->startSession();
        $_SESSION[$name] = $value;
    }

    /**
     * 获取
     * @param string $name
     * @param mixed $defValue
     * @return string
     */
    public function get(string $name, mixed $defValue = ''): mixed
    {
        $this->startSession();
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return $defValue;
    }

    /**
     * 删除
     * @param string $name
     */
    public function delete(string $name): void
    {
        $this->startSession();
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * 清空
     */
    public function clear(): void
    {
        $_SESSION = [];
    }
}