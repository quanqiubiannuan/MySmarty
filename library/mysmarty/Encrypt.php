<?php

namespace library\mysmarty;

/**
 * 加密与解密
 * @package library\mysmarty
 */
class Encrypt
{
    private static ?self $obj = null;
    private string $cipher;
    private string $mode;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    private function init()
    {
        $this->cipher = '';
        $this->mode = '';
    }

    /**
     * 密码学方式
     * @param string $cipher 有效的PHP MCrypt cypher常量
     * @return static
     */
    public function setCipher(string $cipher): static
    {
        $this->cipher = $cipher;
        return $this;
    }

    /**
     * 密码学方式
     * @param string $mode 有效的PHP MCrypt模式常量
     * @return static
     */
    public function setMode(string $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        self::$obj->init();
        return self::$obj;
    }

    /**
     * 加密数据
     * @param string $msg
     * @param string $key 自定义key
     * @return string
     */
    public function encode(string $msg, string $key = ''): string
    {
        if (empty($key)) {
            $key = config('app.encryption_key');
        }
        $method = $this->getMethod();
        $iv = $this->getIv($method);
        return openssl_encrypt($msg, $method, $key, 0, $iv);
    }

    /**
     * 解密数据
     * @param string $data
     * @param string $key
     * @return string
     */
    public function decode(string $data, string $key = ''): string
    {
        if (empty($key)) {
            $key = config('app.encryption_key');
        }
        $method = $this->getMethod();
        $iv = $this->getIv($method);
        return openssl_decrypt($data, $method, $key, 0, $iv);
    }

    /**
     * 获取密码学方式
     * @return string
     */
    private function getMethod(): string
    {
        $method = '';
        if (!empty($this->cipher)) {
            $method = $this->cipher;
        }
        if (!empty($this->mode)) {
            $method = $this->mode;
        }
        if (empty($method)) {
            $method = 'AES-128-CBC';
        }
        return $method;
    }

    /**
     * 获取Vector (iv)
     * @param string $method
     * @return string
     */
    private function getIv(string $method): string
    {
        $ivlen = openssl_cipher_iv_length($method);
        return str_pad('', $ivlen, '1');
    }
}