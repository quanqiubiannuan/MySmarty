<?php

namespace library\mysmarty;

/**
 * 图像处理类
 *
 * @author 戴记
 *
 */
class Image
{

    private $image;

    private $width;

    private $height;

    private $mine;

    private $type;

    private $font = 20;

    private $postion = array(
        0,
        0
    );

    private $positionType = 0;

    private $textcolor;

    private function __construct($image)
    {
        $info = getimagesize($image);
        $this->width = $info[0];
        $this->height = $info[1];
        $this->type = $info[2];
        $this->mine = $info['mime'];
        $this->image = $image;
    }

    private function __clone()
    {
    }

    /**
     * 获取图片资源
     *
     * @return NULL|resource
     */
    private function getIm()
    {
        $im = null;
        switch ($this->type) {
            case 1:
                $im = imagecreatefromgif($this->image);
                break;
            case 2:
                $im = imagecreatefromjpeg($this->image);
                break;
            case 3:
                $im = imagecreatefrompng($this->image);
                break;
        }
        return $im;
    }

    /**
     * 创建图片资源
     *
     * @param int $width
     *            宽度
     * @param int $height
     *            高度的
     * @return resource
     */
    private function createIm($width, $height)
    {
        return imagecreatetruecolor($width, $height);
    }

    /**
     * 保存资源
     *
     * @param resource $im
     * @param string $filename
     * @return string
     */
    private function saveImage($im, $filename)
    {
        if (preg_match('/\.gif/i', $filename)) {
            imagegif($im, $filename);
        } else if (preg_match('/\.jpeg/i', $filename)) {
            imagejpeg($im, $filename);
        } else {
            imagepng($im, $filename);
        }
        return $filename;
    }

    /**
     * 缩放图片
     *
     * @param int $width 缩放到指定宽度
     * @param int $height 缩放到指定高度
     * @param string $filename 缩放图片存放位置
     * @return bool
     */
    public function zoom($width, $height, $filename)
    {
        $dst_image = $this->createIm($width, $height);
        $src_image = $this->getIm();
        if (imagecopyresized($dst_image, $src_image, 0, 0, 0, 0, $width, $height, $this->width, $this->height)) {
            return $this->saveImage($dst_image, $filename);
        }
        return false;
    }

    /**
     * 按照图片宽度等比缩放
     * @param integer $width 缩放到指定宽度
     * @param string $filename 缩放图片存放位置
     * @return bool
     */
    public function zoomWidth($width, $filename)
    {
        $bili = $width / $this->width;
        $height = $bili * $this->height;
        return $this->zoom($width, $height, $filename);
    }

    /**
     * 按照图片高度等比缩放
     * @param integer $height 缩放到指定高度
     * @param string $filename 缩放图片存放位置
     * @return bool
     */
    public function zoomHeight($height, $filename)
    {
        $bili = $height / $this->height;
        $width = $bili * $this->width;
        return $this->zoom($width, $height, $filename);
    }

    /**
     * 截取一部分图像
     *
     * @param int $width
     * @param int $height
     * @param string $filename
     * @return string|boolean
     */
    public function cut($width, $height, $filename)
    {
        $dst_image = $this->createIm($width, $height);
        $src_image = $this->getIm();
        if (imagecopy($dst_image, $src_image, 0, 0, 0, 0, $width, $height)) {
            return $this->saveImage($dst_image, $filename);
        }
        return false;
    }

    /**
     * 创建对象
     *
     * @param string $image 原始图片文件位置
     * @return Image
     */
    public static function image($image)
    {
        return new self($image);
    }

    /**
     * 获取单一实例
     * @param string $image 原始图片文件位置
     * @return Image
     */
    public static function getInstance($image)
    {
        return self::image($image);
    }

    /**
     * 设置字体
     *
     * @param int $font
     * @return Image
     */
    public function font($font)
    {
        $this->font = $font;
        return $this;
    }

    /**
     * 设置文字大小
     * @param integer $font
     * @return Image
     */
    public function setFont($font)
    {
        return $this->font($font);
    }

    /**
     * 设置开始位置
     *
     * @param int $x
     * @param int $y
     * @return Image
     */
    public function position($x, $y)
    {
        $this->postion = array(
            $x,
            $y
        );
        return $this;
    }

    /**
     * 设置水印位置
     * @param integer $x
     * @param integer $y
     * @return Image
     */
    public function setPosition($x, $y)
    {
        return $this->position($x, $y);
    }

    /**
     * 左上位置
     *
     * @return Image
     */
    public function positionTopLeft()
    {
        $this->positionType = 1;
        return $this;
    }

    /**
     * 右上位置
     *
     * @return Image
     */
    public function positionTopRight()
    {
        $this->positionType = 2;
        return $this;
    }

    /**
     * 左下位置
     *
     * @return Image
     */
    public function positionBottomLeft()
    {
        $this->positionType = 3;
        return $this;
    }

    /**
     * 右下位置
     *
     * @return Image
     */
    public function positionBottomRight()
    {
        $this->positionType = 4;
        return $this;
    }

    /**
     * 中间位置
     *
     * @return Image
     */
    public function positionCenter()
    {
        $this->positionType = 5;
        return $this;
    }

    /**
     * 设置颜色
     *
     * @param int $r
     *            0-255
     * @param int $g
     *            0-255
     * @param int $b
     *            0-255
     * @return Image
     */
    public function color($r, $g, $b)
    {
        $this->textcolor = array(
            $r,
            $g,
            $b
        );
        return $this;
    }

    /**
     * 设置水印文字为红色
     * @return Image
     */
    public function setRedColor()
    {
        return $this->color(255, 0, 0);
    }

    /**
     * 设置水印文字为黑色
     * @return Image
     */
    public function setBlackColor()
    {
        return $this->color(0, 0, 0);
    }

    /**
     * 设置水印文字为橙色
     * @return Image
     */
    public function setOrangeColor()
    {
        return $this->color(255, 165, 0);
    }

    /**
     * 设置水印文字为Aliceblue
     * @return Image
     */
    public function setAliceblueColor()
    {
        return $this->color(240, 248, 255);
    }

    /**
     * 设置水印文字为蓝色
     * @return Image
     */
    public function setBlueColor()
    {
        return $this->color(0, 0, 255);
    }

    /**
     * 设置水印文字为绿色
     * @return Image
     */
    public function setGreenColor()
    {
        return $this->color(0, 128, 0);
    }

    /**
     * 设置水印文字为黄色
     * @return Image
     */
    public function setYellowColor()
    {
        return $this->color(255, 255, 0);
    }

    /**
     * 设置水印文字为粉色
     * @return Image
     */
    public function setPinkColor()
    {
        return $this->color(255, 192, 203);
    }

    /**
     * 设置水印
     *
     * @param string $string 水印文字或水印图片位置
     * @param string $filename
     *            保存文件
     * @param string $fontfile
     *            字体文件名称，需要放在 \extend\fonts 目录
     * @return string|bool
     * @throws
     */
    public function water($string, $filename = '', $fontfile = 'zkklt.ttf')
    {
        $font_file = ROOT_DIR . '/extend/fonts/' . $fontfile;
        $src_image = $this->getIm();
        if ($this->textcolor) {
            $textcolor = imagecolorallocate($src_image, $this->textcolor[0], $this->textcolor[1], $this->textcolor[2]);
        } else {
            $textcolor = imagecolorallocate($src_image, 0, 0, 0);
        }
        $dst_im = null;
        if (file_exists($string)) {
            $dst_im = self::image($string);
        }
        $len = 1;
        if (file_exists($string)) {
            $fontx = $dst_im->width;
            $fonty = $dst_im->height;
        } else {
            $len = mb_strlen($string, 'utf-8');
            // 计算坐标
            $imgInfo = imagettfbbox($this->font, 0, $font_file, $string);
            //验证码总长度
            $code_len = $imgInfo[2] - $imgInfo[0];
            $fontx = $code_len / $len;
            $fonty = $imgInfo[3] - $imgInfo[5];

        }
        switch ($this->positionType) {
            case 1:
                $this->position(0, 0);
                break;
            case 2:
                $startx = $this->width - $len * $fontx;
                if ($startx < 0) {
                    $startx = 0;
                }
                $this->position($startx, 0);
                break;
            case 3:
                $starty = $this->height - $fonty;
                if ($starty < 0) {
                    $starty = 0;
                }
                $this->position(0, $starty);
                break;
            case 4:
                $startx = $this->width - $len * $fontx;
                if ($startx < 0) {
                    $startx = 0;
                }
                $starty = $this->height - $fonty;
                if ($starty < 0) {
                    $starty = 0;
                }
                $this->position($startx, $starty);
                break;
            case 5:
                $startx = (int)(($this->width - $len * $fontx) / 2);
                $starty = (int)(($this->height - $fonty) / 2);
                if ($startx < 0) {
                    $startx = 0;
                }
                if ($starty < 0) {
                    $starty = 0;
                }
                $this->position($startx, $starty);
                break;
        }
        if (!file_exists($string)) {
            imagefttext($src_image, $this->font, 0, $this->postion[0], $this->postion[1] + $fonty, $textcolor, $font_file, $string);
        } else {
            // 是文件
            if (!imagecopy($src_image, $dst_im->getIm(), $this->postion[0], $this->postion[1], 0, 0, $dst_im->width, $dst_im->height)) {
                return false;
            }
        }
        if (empty($filename)) {
            header('Content-type: image/png');
            imagepng($src_image);
            imagedestroy($src_image);
            exit();
        } else {
            return $this->saveImage($src_image, $filename);
        }
    }
}