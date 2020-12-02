<?php

namespace library\mysmarty;

use GdImage;

/**
 * 图像处理类
 */
class Image
{

    private string $image;

    private int $width;

    private int $height;

    private string $mine;

    private int $type;

    private int $font = 20;

    private array $postion = [0, 0];

    private int $positionType = 0;

    private array $textcolor;

    /**
     * 图片构造器
     * @param string $image 图片位置
     */
    private function __construct(string $image)
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
     * @return GdImage|bool
     */
    private function getIm(): GdImage|bool
    {
        $im = false;
        $im = match ($this->type) {
            1 => imagecreatefromgif($this->image),
            2 => imagecreatefromjpeg($this->image),
            3 => imagecreatefrompng($this->image),
        };
        return $im;
    }

    /**
     * 创建图片资源
     * @param int $width 宽度
     * @param int $height 高度
     * @return GdImage|bool
     */
    private function createIm(int $width, int $height): GdImage|bool
    {
        return imagecreatetruecolor($width, $height);
    }

    /**
     * 保存资源
     * @param GdImage $im
     * @param string $filename
     * @return string
     */
    private function saveImage(GdImage $im, string $filename): string
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
     * @return string|bool
     */
    public function zoom(int $width, int $height, string $filename): bool|string
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
     * @param int $width 缩放到指定宽度
     * @param string $filename 缩放图片存放位置
     * @return bool|string
     */
    public function zoomWidth(int $width, string $filename): bool|string
    {
        $bili = $width / $this->width;
        $height = $bili * $this->height;
        return $this->zoom($width, $height, $filename);
    }

    /**
     * 按照图片高度等比缩放
     * @param int $height 缩放到指定高度
     * @param string $filename 缩放图片存放位置
     * @return bool|string
     */
    public function zoomHeight(int $height, string $filename): bool|string
    {
        $bili = $height / $this->height;
        $width = $bili * $this->width;
        return $this->zoom($width, $height, $filename);
    }

    /**
     * 截取一部分图像
     * @param int $width
     * @param int $height
     * @param string $filename
     * @return string|boolean
     */
    public function cut(int $width, int $height, string $filename): bool|string
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
     * @param string $image 原始图片文件位置
     * @return static
     */
    public static function image(string $image): static
    {
        return new self($image);
    }

    /**
     * 获取单一实例
     * @param string $image 原始图片文件位置
     * @return static
     */
    public static function getInstance(string $image): static
    {
        return self::image($image);
    }

    /**
     * 设置字体
     * @param int $font
     * @return static
     */
    public function font(int $font): static
    {
        $this->font = $font;
        return $this;
    }

    /**
     * 设置文字大小
     * @param int $font
     * @return static
     */
    public function setFont(int $font): static
    {
        return $this->font($font);
    }

    /**
     * 设置开始位置
     * @param int $x
     * @param int $y
     * @return static
     */
    public function position(int $x, int $y): static
    {
        $this->postion = [$x, $y];
        return $this;
    }

    /**
     * 设置水印位置
     * @param int $x
     * @param int $y
     * @return static
     */
    public function setPosition(int $x, int $y): static
    {
        return $this->position($x, $y);
    }

    /**
     * 左上位置
     * @return static
     */
    public function positionTopLeft(): static
    {
        $this->positionType = 1;
        return $this;
    }

    /**
     * 右上位置
     * @return static
     */
    public function positionTopRight(): static
    {
        $this->positionType = 2;
        return $this;
    }

    /**
     * 左下位置
     * @return static
     */
    public function positionBottomLeft(): static
    {
        $this->positionType = 3;
        return $this;
    }

    /**
     * 右下位置
     * @return static
     */
    public function positionBottomRight(): static
    {
        $this->positionType = 4;
        return $this;
    }

    /**
     * 中间位置
     * @return static
     */
    public function positionCenter(): static
    {
        $this->positionType = 5;
        return $this;
    }

    /**
     * 设置颜色
     * @param int $r 0-255
     * @param int $g 0-255
     * @param int $b 0-255
     * @return static
     */
    public function color(int $r, int $g, int $b): static
    {
        $this->textcolor = [$r, $g, $b];
        return $this;
    }

    /**
     * 设置水印文字为红色
     * @return static
     */
    public function setRedColor(): static
    {
        return $this->color(255, 0, 0);
    }

    /**
     * 设置水印文字为黑色
     * @return static
     */
    public function setBlackColor(): static
    {
        return $this->color(0, 0, 0);
    }

    /**
     * 设置水印文字为橙色
     * @return static
     */
    public function setOrangeColor(): static
    {
        return $this->color(255, 165, 0);
    }

    /**
     * 设置水印文字为Aliceblue
     * @return static
     */
    public function setAliceblueColor(): static
    {
        return $this->color(240, 248, 255);
    }

    /**
     * 设置水印文字为蓝色
     * @return static
     */
    public function setBlueColor(): static
    {
        return $this->color(0, 0, 255);
    }

    /**
     * 设置水印文字为绿色
     * @return static
     */
    public function setGreenColor(): static
    {
        return $this->color(0, 128, 0);
    }

    /**
     * 设置水印文字为黄色
     * @return static
     */
    public function setYellowColor(): static
    {
        return $this->color(255, 255, 0);
    }

    /**
     * 设置水印文字为粉色
     * @return static
     */
    public function setPinkColor(): static
    {
        return $this->color(255, 192, 203);
    }

    /**
     * 设置水印
     *
     * @param string $string 水印文字或水印图片位置
     * @param string $filename 保存文件
     * @param string $fontfile 字体文件名称，需要放在 \extend\fonts 目录
     * @return string|bool
     */
    public function water(string $string, string $filename = '', string $fontfile = 'zkklt.ttf'): bool|string
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