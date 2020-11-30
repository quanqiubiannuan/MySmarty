<?php

namespace library\mysmarty;

/**
 * session操作类
 *
 * @author 戴记
 *
 */
class Session
{

    private $lifetime;

    private $path;

    private $domain;

    private $secure;

    private $httponly;

    private static $obj;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     *
     * @return mixed
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     *
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     *
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     *
     * @return mixed
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     *
     * @return mixed
     */
    public function getHttponly()
    {
        return $this->httponly;
    }

    /**
     * Cookie 的 生命周期，以秒为单位。
     *
     * @param string $lifetime
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * 此 cookie 的有效 路径。 on the domain where 设置为“/”表示对于本域上所有的路径此 cookie 都可用。
     *
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Cookie 的作用 域。 例如：“www.php.net”。 如果要让 cookie 在所有的子域中都可用，此参数必须以点（.）开头，例如：“.php.net”。
     *
     * @param string $domain
     * @return $this
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * 设置为 TRUE 表示 cookie 仅在使用 安全 链接时可用。
     *
     * @param bool $secure
     * @return $this
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * 设置为 TRUE 表示 PHP 发送 cookie 的时候会使用 httponly 标记。
     *
     * @param bool $httponly
     * @return $this
     */
    public function setHttponly($httponly)
    {
        $this->httponly = $httponly;
        return $this;
    }

    /**
     * 开启session
     */
    public function startSession()
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
     *
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        $this->startSession();
        $_SESSION[$name] = $value;
    }

    /**
     * 获取
     *
     * @param string $name
     * @param string $defValue
     * @return string
     */
    public function get($name, $defValue = '')
    {
        $this->startSession();
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return $defValue;
    }

    /**
     * 删除
     *
     * @param string $name
     */
    public function delete($name)
    {
        $this->startSession();
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    /**
     * 清空
     */
    public function clear()
    {
        $_SESSION = [];
    }
}