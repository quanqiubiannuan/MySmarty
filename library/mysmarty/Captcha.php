<?php

namespace library\mysmarty;

/**
 * 验证码类
 *
 * @author 戴记
 *
 */
class Captcha
{

    /**
     * 图像宽度
     *
     * @var int
     */
    private $width = 0;

    /**
     * 图像高度
     *
     * @var int
     */
    private $height = 50;

    /**
     * 图像上显示的字符
     *
     * @var int
     */
    private $code;

    /**
     * 验证码类型
     * @var int 0 数字与字母，1 数字，2 字母，3 中文
     */
    private $codeStyle = 0;

    //验证码长度
    private $codeSize = 4;

    /**
     * 字体大小
     *
     * @var int
     */
    private $font = 25;

    /**
     * 验证码有效期，单位秒
     * @var int
     */
    private $lifttime = 300;
    /**
     * 字体文件
     * @var string
     */
    private $fontFile = 'times.ttf';

    /**
     * 验证码session名称
     * @var string
     */
    private static $sessionName = 'code';

    /**
     * 构造方法
     *
     * @param string $code
     *            图像字符串
     * @param integer $width
     *            图像宽度
     * @param integer $height
     *            图像高度
     */
    private function __construct($code = null, $width = null, $height = null)
    {
        if ($code !== null) {
            if (empty($code)) {
                $code = $this->getCode();
            }
            $this->code = (string)$code;
        }
        if ($width !== null) {
            $this->width = $width;
        }
        if ($height !== null) {
            $this->height = $height;
        }
    }

    /**
     * 获取单一实例
     * @return $this
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * 设置字体我呢见
     * @param string $fontFile 字体文件名称
     */
    public function setFontFile($fontFile)
    {
        $this->fontFile = $fontFile;
        return $this;
    }

    /**
     * 设置字体大小
     * @param integer $font
     * @return $this
     */
    public function setFont($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * 设置验证码session名称
     * @param string $sesssionName
     * @return $this
     */
    public function setSessionName($sesssionName)
    {
        self::$sessionName = $sesssionName;
        return $this;
    }

    /**
     * 设置验证码宽度
     * @param integer $width
     * @return $this
     */
    public function setWidth($width)
    {
        return $this;
    }

    /**
     * 设置验证码高度
     * @param integer $height
     * @return $this
     */
    public function setHeight($height)
    {
        return $this;
    }

    /**
     * 设置验证码字符
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * 设置验证码类型
     * @param integer $codeStyle 0 数字与字母，1 数字，2 字母，3 中文
     * @return $this
     */
    public function setCodeStyle($codeStyle)
    {
        $this->codeStyle = $codeStyle;
        return $this;
    }

    /**
     * 设置验证码长度
     * @param integer $codeSize
     * @return $this
     */
    public function setCodeSize($codeSize)
    {
        $this->codeSize = $codeSize;
        return $this;
    }

    /**
     * 设置验证码有效时间
     * @param integer $lifetime
     * @return $this
     */
    public function setLifetime($lifetime)
    {
        $this->lifttime = $lifetime;
        return $this;
    }

    private function __clone()
    {
    }

    /**
     * 静态调用方法
     *
     * @param integer $width
     *            图像宽度
     * @param integer $height
     *            图像高度
     * @return $this
     */
    public static function code($width = 100, $height = 50)
    {
        return new self('', $width, $height);
    }

    /**
     * 获取随机字符串
     *
     * @return string
     */
    private function getCode()
    {
        $str = '0123456789qwertyuioplkjhgfdsazxcvbnm' . strtoupper('qwertyuioplkjhgfdsazxcvbnm');
        return $this->getCodeByStr($str);
    }

    /**
     * 获取随机一个字符串
     *
     * @return string
     */
    private function getOneCode()
    {
        $str = '0123456789qwertyuioplkjhgfdsazxcvbnm' . strtoupper('qwertyuioplkjhgfdsazxcvbnm');
        return substr(str_shuffle($str), 0, 1);
    }

    /**
     * 获取指定的字符串数据
     * @param integer $str
     * @return string
     */
    private function getCodeByStr($str)
    {
        return substr(str_shuffle($str), 0, $this->codeSize);
    }

    /**
     * 获取数字验证码
     * @return string
     */
    private function getNumCode()
    {
        $str = '0123456789';
        return $this->getCodeByStr($str);
    }

    /**
     * 获取字母验证码
     * @return string
     */
    private function getLetterCode()
    {
        $str = 'qwertyuioplkjhgfdsazxcvbnm' . strtoupper('qwertyuioplkjhgfdsazxcvbnm');
        return $this->getCodeByStr($str);
    }

    /**
     * 获取中文验证码
     * @return string
     */
    private function getZhCode()
    {
        return getZhChar($this->codeSize);
    }

    /**
     * 设置字体
     * @param integer $font
     * @return $this
     */
    public function font($font)
    {
        return $this->setFont($font);
    }

    /**
     * 输出验证码
     */
    public function output()
    {
        header('Content-Type: image/png');
        $im = $this->generateImage();
        imagepng($im);
        imagedestroy($im);
        exit();
    }

    /**
     * 生成验证码
     */
    private function generateImage()
    {
        $kWidth = 20;
        if (empty($this->code)) {
            switch ($this->codeStyle) {
                case 0:
                    $this->code = $this->getCode();
                    break;
                case 1:
                    $this->code = $this->getNumCode();
                    break;
                case 2:
                    $this->code = $this->getLetterCode();
                    break;
                case 3:
                    $this->code = $this->getZhCode();
                    $kWidth = 30;
                    break;
                default:
                    $this->code = $this->getCode();
            }
        }
        Session::getInstance()->set(self::$sessionName, strtolower($this->code));
        // 创建画布
        $codeArr = preg_split('//u', $this->code);
        $codeLen = count($codeArr);
        $this->width = $codeLen * $kWidth;
        $im = @imagecreatetruecolor($this->width, $this->height);
        // 背景颜色
        $backgroundColor = imagecolorallocatealpha($im, 243, 251, 254, 0);
        imagefilledrectangle($im, 0, 0, $this->width - 1, $this->height - 1, $backgroundColor);
        // 计算坐标
        $imgInfo = imagettfbbox($this->font, 0, ROOT_DIR . '/extend/fonts/' . $this->fontFile, $this->code);
        //开始y位置
        $y = ($this->height - $imgInfo[3] - $imgInfo[5]) / 2;
        // 写字符串
        for ($i = 0; $i < $codeLen; $i++) {
            $angle = random_int(-20, 20);
            $text_color = imagecolorallocate($im, random_int(0, 100), random_int(0, 100), random_int(0, 100));
            // 画验证码
            $x = $i * $kWidth;
            imagefttext($im, $this->font, $angle, $x, $y, $text_color, ROOT_DIR . '/extend/fonts/' . $this->fontFile, $codeArr[$i]);
            $text_color = imagecolorallocate($im, random_int(150, 255), random_int(150, 255), random_int(150, 255));
            imagestring($im, 5, $x, $y + random_int(-1 * $y, 5), $this->getOneCode(), $text_color);
            // 画干扰线
            imageline($im, random_int(0, $this->width), random_int(0, $this->height), random_int(0, $this->width), random_int(0, $this->height), $text_color);
        }
        return $im;
    }

    /**
     * 生成验证码的base64编码
     * @return string
     */
    public function getBase64Image()
    {
        $im = $this->generateImage();
        $fileName = RUNTIME_DIR . '/captcha/captcha.png';
        createDirByFile($fileName);
        imagepng($im, $fileName);
        imagedestroy($im);
        $imgContent = file_get_contents($fileName);
        $imgEncode = base64_encode($imgContent);
        $imgInfo = getimagesize($fileName);
        return "data:{$imgInfo['mime']};base64," . $imgEncode;
    }

    /**
     * 验证验证码
     *
     * @param string $code
     *            输入的字符
     * @param string $sessionName 验证码session名称
     * @return boolean
     */
    public static function check($code, $sessionName = 'code')
    {
        if (getSession($sessionName) === strtolower($code)) {
            deleteSession($sessionName);
            return true;
        }
        return false;
    }
}