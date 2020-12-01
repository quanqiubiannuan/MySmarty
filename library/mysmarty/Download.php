<?php
/**
 * Date: 2019/5/6
 * Time: 9:00
 */

namespace library\mysmarty;

class Download
{
    private static ?self $obj = null;

    private string $downloadUrl;

    private string $fileExtension;

    private string $saveDir;

    private string $saveFilename;

    private int $timeOut = 60;

    private function init()
    {
        $this->downloadUrl = '';
        $this->fileExtension = '';
        $this->saveDir = '';
        $this->saveFilename = '';
        $this->timeOut = 60;
    }

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
        self::$obj->init();
        return self::$obj;
    }

    /**
     * 设置下载链接
     * @param string $downloadUrl
     * @return static
     */
    public function setDownloadUrl(string $downloadUrl): static
    {
        $this->downloadUrl = $downloadUrl;
        return $this;
    }

    /**
     * 设置下载文件的后缀，不包括 .
     * @param string $fileExtension
     * @return static
     */
    public function setFileExtension(string $fileExtension): static
    {
        $this->fileExtension = $fileExtension;
        return $this;
    }

    /**
     * 设置下载文件的保存目录
     * @param string $saveDir
     * @return static
     */
    public function setSaveDir(string $saveDir): static
    {
        $this->saveDir = $saveDir;
        return $this;
    }

    /**
     * 设置保存的文件名，包含文件名后缀
     * @param string $saveFilename
     * @return static
     */
    public function setSaveFilename(string $saveFilename): static
    {
        $this->saveFilename = $saveFilename;
        return $this;
    }

    /**
     * 设置下载超时时间
     * @param int $timeOut 单位，秒
     * @return static
     */
    public function setTimeOut(int $timeOut): static
    {
        $this->timeOut = $timeOut;
        return $this;
    }

    /**
     * 开始下载文件
     * @return bool|string 下载成功返回保存的文件名
     */
    public function download(): bool|string
    {
        if (0 !== stripos($this->downloadUrl, "http")) {
            return false;
        }
        $fileData = Query::getInstance()->setPcUserAgent()
            ->setUrl($this->downloadUrl)
            ->setTimeOut($this->timeOut)
            ->setRandIp()
            ->send();
        if (empty($fileData)) {
            return false;
        }
        if (empty($this->saveDir)) {
            $this->saveDir = PUBLIC_DIR . '/upload/' . date('Ymd');
        }
        //创建文件夹
        if (!file_exists($this->saveDir)) {
            if (!mkdir($this->saveDir, 0777, true)) {
                return false;
            }
        }
        if (empty($this->saveFilename)) {
            if (empty($this->fileExtension)) {
                $parseArr = parse_url($this->downloadUrl);
                if (!empty($parseArr['path'])) {
                    $this->fileExtension = pathinfo($parseArr['path'], PATHINFO_EXTENSION);
                } else {
                    $this->fileExtension = pathinfo($this->downloadUrl, PATHINFO_EXTENSION);
                }
            }
            $this->saveFilename = md5(time() . rand(1000, 9999) . $this->downloadUrl);
            if (!empty($this->fileExtension)) {
                $this->saveFilename .= '.' . $this->fileExtension;
            }
        }
        $filename = rtrim($this->saveDir, '/') . '/' . $this->saveFilename;
        if (file_put_contents($filename, $fileData)) {
            return str_ireplace(PUBLIC_DIR, '', $filename);
        }
        return false;
    }
}