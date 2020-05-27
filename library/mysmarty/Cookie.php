<?php

namespace library\mysmarty;

class Cookie
{

    private $expire;

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
    public function getExpire()
    {
        return $this->expire;
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
     *
     * @param integer $expire
     * @return $this
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
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
     * 设置
     *
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        $lifetime = $this->expire ?: config('session.lifetime', 3600);
        $path = $this->path ?: config('session.path', '/');
        $domain = $this->domain ?: config('session.domain', '');
        $secure = $this->secure ?: config('session.secure', false);
        $httponly = $this->httponly ?: config('session.httponly', true);
        if (empty($domain)) {
            $domain = $_SERVER['SERVER_NAME'];
        }
        setcookie($name, $value, time() + intval($lifetime), $path, $domain, $secure, $httponly);
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
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
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
        if (isset($_COOKIE[$name])) {
            $path = $this->path ?: config('session.path', '/');
            $domain = $this->domain ?: config('session.domain', '');
            $secure = $this->secure ?: config('session.secure', false);
            $httponly = $this->httponly ?: config('session.httponly', true);
            if (empty($domain)) {
                $domain = $_SERVER['SERVER_NAME'];
            }
            setcookie($name, '', time() - 3600, $path, $domain, $secure, $httponly);
        }
    }

    /**
     * 清空
     */
    public function clear()
    {
        if (count($_COOKIE) > 1) {
            $path = $this->path ?: config('session.path', '/');
            $domain = $this->domain ?: config('session.domain', '');
            $secure = $this->secure ?: config('session.secure', false);
            $httponly = $this->httponly ?: config('session.httponly', true);
            if (empty($domain)) {
                $domain = $_SERVER['SERVER_NAME'];
            }
            foreach ($_COOKIE as $k => $v) {
                if ($k === 'PHPSESSID') {
                    continue;
                }
                setcookie($k, '', time() - 3600, $path, $domain, $secure, $httponly);
            }
        }
    }
}