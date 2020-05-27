<?php
/**
 * Date: 2019/5/7
 * Time: 9:05
 */

namespace library\mysmarty;

/**
 * 加密与解密
 * @package library\mysmarty
 */
class Encrypt
{
    private static $obj;

    private $cipher;

    private $mode;


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
     * @param mixed $cipher 有效的PHP MCrypt cypher常量
     * @return $this
     */
    public function setCipher($cipher)
    {
        $this->cipher = $cipher;
        return $this;
    }

    /**
     * 密码学方式
     * @param mixed $mode 有效的PHP MCrypt模式常量
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    public static function getInstance()
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
    public function encode($msg, $key = '')
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
    public function decode($data, $key = '')
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
    private function getMethod()
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
     * @param mixed $method
     * @return string
     */
    private function getIv($method)
    {
        $ivlen = openssl_cipher_iv_length($method);
        return str_pad('', $ivlen, '1');
    }
}