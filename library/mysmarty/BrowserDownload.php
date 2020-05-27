<?php

namespace library\mysmarty;
/**
 * 浏览器下载
 * @package library\mysmarty
 */
class BrowserDownload
{
    // 文件数据
    private $data;
    // 文件类型
    private $mimeType;
    // 响应过期时间
    private $expire = 360;
    // 下载文件名
    private $downloadFileName;
    private static $obj;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 获取实例
     * @return BrowserDownload
     * @author 戴记
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 设置文件链接
     * @param string $url
     * @return BrowserDownload
     */
    public function setUrl($url)
    {
        if (0 !== stripos($url, 'http')) {
            exit('链接错误');
        }
        $this->data = file_get_contents($url);
        $urlPath = parse_url($url, PHP_URL_PATH);
        $this->downloadFileName = pathinfo($urlPath, PATHINFO_BASENAME);
        return $this;
    }

    /**
     * 设置文件数据
     * @param string $data
     * @return BrowserDownload
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * 设置文件所在位置
     * @param string $file
     * @return BrowserDownload
     */
    public function setFile($file)
    {
        if (!file_exists($file)) {
            exit('文件不存在');
        }
        $this->data = file_get_contents($file);
        $this->mimeType = mime_content_type($file);
        $this->downloadFileName = pathinfo($file, PATHINFO_BASENAME);
        return $this;
    }

    /**
     * 设置文件类型
     * @param string $mimeType
     * @return $this
     * @author 戴记
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * 设置响应过期时间
     * @param int $expire 单位：秒
     * @return $this
     * @author 戴记
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * 输出文件
     * @param string $downloadFileName 文件下载名
     * @throws \Exception
     * @author 戴记
     */
    public function output($downloadFileName = '')
    {
        if (empty($downloadFileName)) {
            if (!empty($this->downloadFileName)) {
                $downloadFileName = $this->downloadFileName;
            } else {
                $downloadFileName = md5(time() . random_int(1000, 9999));
            }
        }
        if (empty($this->data)) {
            exit('下载文件为空');
        }
        header_remove();
        header('Pragma: public');
        header('Content-Type: ' . ($this->mimeType ?: 'application/octet-stream'));
        header('Cache-control: max-age=' . $this->expire);
        header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
        header('Content-Length: ' . strlen($this->data));
        header('Content-Transfer-Encoding: binary');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $this->expire) . ' GMT');
        exit($this->data);
    }
}