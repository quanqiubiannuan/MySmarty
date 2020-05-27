<?php

namespace library\mysmarty;

/*
 * 采集类
 */

class Caiji
{

    private $url;

    private $ip;

    private $refer;

    private $cookiefile;

    private $agent;

    private $post;

    private $header;

    private $time;

    private $sslVerifypeer;

    public function __construct($url)
    {
        $this->url = $url;
        $this->ip = '';
        $this->refer = '';
        $this->cookiefile = '';
        $this->agent = '';
        $this->post = array();
        $this->header = array();
        $this->time = 0;
        $this->sslVerifypeer = false;
    }

    /**
     * 设置方法
     * @param string $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    public function setRefer($refer)
    {
        $this->refer = $refer;
    }

    public function setCookieFile($cookiefile)
    {
        $this->cookiefile = $cookiefile;
    }

    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    public function setPost($post)
    {
        $this->post = $post;
    }

    /**
     * 设置header
     *
     * @param array $header
     *            数组
     */
    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function setTime($time)
    {
        $this->time = $time;
    }

    /**
     * 获取网址内容
     */
    public function getRes()
    {
        // 使用curl获取内容
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (!empty($this->time)) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->time);
        }
        // 设置浏览器
        if (!empty($this->agent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->agent);
        }
        // 设置cookie
        if (!empty($this->cookiefile)) {
            if (!file_exists($this->cookiefile)) {
                file_put_contents($this->cookiefile, ''); // 创建cookie文件
            }
            $cookiefile = realpath($this->cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
        }
        // post发送
        if (!empty($this->post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
        }
        if (!empty($this->header)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }
        // 伪造ip
        if (!empty($this->ip)) {
            if (!empty($this->header)) {
                $this->header[] = 'X-FORWARDED-FOR:' . $this->ip;
                $this->header[] = 'CLIENT-IP:' . $this->ip;
            } else {
                $this->header = array(
                    'X-FORWARDED-FOR:' . $this->ip,
                    'CLIENT-IP:' . $this->ip
                );
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        }
        // 伪造来源
        if (!empty($this->refer)) {
            curl_setopt($ch, CURLOPT_REFERER, $this->refer);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->sslVerifypeer);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    /**
     * 获取网址内容2
     */
    public function getRes2()
    {
        $res = file_get_contents($this->url);
        if ($res !== false) {
            return $res;
        }
        return false;
    }
}