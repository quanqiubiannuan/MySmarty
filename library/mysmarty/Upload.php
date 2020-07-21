<?php

namespace library\mysmarty;

/**
 * 文件上传类
 *
 * @author 戴记
 *
 */
class Upload
{

    private static $obj = null;

    private $limitType = [];

    private $limitSize = '';

    private $limitExt = '';

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
        self::$obj->initVariable();
        return self::$obj;
    }

    /**
     * 移动文件
     *
     * @param string $name
     *            表单提交文件名字段
     * @return boolean|array|string 成功返回路径，失败返回false
     */
    public function move($name)
    {
        $files = $_FILES[$name] ?? '';
        if (empty($files)) {
            return false;
        }
        if (is_array($files['name'])) {
            $result = [];
            $len = count($files['name']);
            for ($i = 0; $i < $len; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                $result[] = $this->myMove($file);
            }
            return $result;
        }
        return $this->myMove($files);
    }

    /**
     * 设置上传文件类型
     *
     * @param array|string $type
     *            如：['image/png']
     * @return $this
     */
    public function setLimitType($type)
    {
        if (!is_array($type)) {
            $type = explode(',', $type);
        }
        $this->limitType = $type;
        return $this;
    }

    /**
     * 设置上传文件大小
     *
     * @param int $size
     *            字节， 1kb = 1024b
     * @return Upload
     */
    public function setLimitSize($size)
    {
        $this->limitSize = $size;
        return $this;
    }

    /**
     * 设置上传文件后缀，不包括.
     *
     * @param string $ext
     *            如：png
     * @return Upload
     */
    public function setLimitExt($ext)
    {
        $this->limitExt = $ext;
        return $this;
    }

    /**
     * @param array $file
     * @return bool|string
     */
    private function myMove($file)
    {
        if ($file['error'] !== 0) {
            return false;
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        if (!empty($this->limitType) && !in_array($file['type'], $this->limitType, true)) {
            return false;
        }

        if (!empty($this->limitSize) && $file['size'] > $this->limitSize) {
            return false;
        }

        $ext = MimeType::getExt($file['type']);
        if (empty($ext)) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            if (empty($ext)) {
                return false;
            }
        }

        if (!empty($this->limitExt) && $ext !== $this->limitExt) {
            return false;
        }
        $pathDir = '/upload/' . date('Ymd');
        $dir = ROOT_DIR . '/public' . $pathDir;
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }
        $filename = md5(time() . $file['name']) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
            return $pathDir . '/' . $filename;
        }
        return false;
    }

    private function initVariable()
    {
        $this->limitExt = '';
        $this->limitSize = '';
        $this->limitType = '';
    }
}