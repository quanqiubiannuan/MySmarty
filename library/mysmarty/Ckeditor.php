<?php
/**
 * Date: 2019/5/29
 * Time: 13:55
 */

namespace library\mysmarty;

/**
 * 富文本编辑器类
 * @package library\mysmarty
 */
class Ckeditor
{
    private static $obj;
    private $allowTags = '<p><img><h1><h2><h3><h4><h5><h6><strong><i><a><ul><li><ol><blockquote><table><thead><tbody><tr><th><td><pre><code><br>';

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 获取Ckeditor对象
     * @return Ckeditor
     */
    public static function getInstance()
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 获取编辑器格式化后内容
     * @param string $content 编辑器内容
     * @param bool $downloadImg 是否自动下载远程图片
     * @return string
     */
    public function getContent($content, $downloadImg = true)
    {
        $content = $this->stripTags($content);
        $content = $this->paiban($content, $downloadImg);
        return $content;
    }

    /**
     * 去掉编辑器的不可信标签
     * @param string $content
     * @return string
     */
    private function stripTags($content)
    {
        $content = str_ireplace('<p></p>', '', $content);
        $content = preg_replace('/<p>([ 　]|&nbsp;)+<\/p>/iUu', '', $content);
        $content = str_ireplace('figure', 'p', $content);
        $content = preg_replace('/<figcaption>.*<\/figcaption>/iU', '', $content);
        //去掉样式标签
        $allowTagArr = explode('>', $this->allowTags);
        foreach ($allowTagArr as $v) {
            if (empty($v)) {
                continue;
            }
            if ($v === '<a') {
                $content = preg_replace('/<a [^>]*(href=[\'"][^\'"]+[\'"])[^>]*>/iU', '<a $1>', $content);
            } else if ($v === '<img') {
                $content = $this->replaceImg($content);
            } else if ($v === '<code') {
                $content = $this->replaceCode($content);
            } else if ($v === '<pre') {
                $content = preg_replace('/<pre[^>]*>/iU', '<p><pre>', $content);
                $content = preg_replace('/<\/pre>/iU', '</pre></p>', $content);
            } else {
                $content = preg_replace('/' . preg_quote($v, '/') . ' [^>]*>/iUs', $v . '>', $content);
            }
        }
        $content = str_ireplace('<a ', '<a rel="nofollow" target="_blank" ', $content);
        //多个换行替换为一个换行
        $content = preg_replace('/(<br[^>]*>){2,}/i', '<br>', $content);
        return myTrim(strip_tags($content, $this->allowTags));
    }

    /**
     * 替换内容中的图片
     * @param string $content 内容
     * @return mixed
     */
    private function replaceImg($content)
    {
        $reg = '/<img[^>]*>/iU';
        if (preg_match_all($reg, $content, $mat)) {
            foreach ($mat[0] as $k => $v) {
                $repImg = '<img';
                $src = $this->getImgAttr($v, 'src');
                if (empty($src)) {
                    continue;
                }
                $repImg .= ' src="' . $src . '"';
                $height = $this->getImgAttr($v, 'height');
                if (!empty($height)) {
                    $repImg .= ' height="' . $height . '"';
                }
                $width = $this->getImgAttr($v, 'width');
                if (!empty($width)) {
                    $repImg .= ' width="' . $width . '"';
                }
                $repImg .= '>';
                $content = str_ireplace($v, $repImg, $content);
            }
        }
        return $content;
    }

    /**
     * 替换内容中的代码部分
     * @param string $content 内容
     * @return mixed
     */
    private function replaceCode($content)
    {
        $reg = '/<code[^>]*>/iU';
        if (preg_match_all($reg, $content, $mat)) {
            foreach ($mat[0] as $k => $v) {
                $repCode = '<code';
                $class = $this->getCodeAttr($v, 'class');
                if (!empty($class)) {
                    $repCode .= ' class="' . $class . '"';
                }
                $repCode .= '>';
                $content = str_ireplace($v, $repCode, $content);
            }
        }
        return $content;
    }

    /**
     * 获取图片的属性标签值
     * @param string $img img标签代码
     * @param string $attr img属性值
     * @return string
     */
    private function getImgAttr($img, $attr)
    {
        $reg = '/<img [^>]*' . $attr . '=[\'"]([^\'"]+)[\'"][^>]*>/iU';
        if (preg_match($reg, $img, $mat)) {
            return myTrim($mat[1]);
        }
        return '';
    }

    /**
     * 获取code标签的属性标签值
     * @param string $code img标签代码
     * @param string $attr img属性值
     * @return string
     */
    private function getCodeAttr($code, $attr)
    {
        $reg = '/<code [^>]*' . $attr . '=[\'"]([^\'"]+)[\'"][^>]*>/iU';
        if (preg_match($reg, $code, $mat)) {
            return myTrim($mat[1]);
        }
        return '';
    }

    /**
     * 对内容排版
     * @param string $str
     * @param bool $downloadImg 是否下载远程图片
     * @return string
     */
    private function paiban($str, $downloadImg = true)
    {
        $isHtmlspecialchars = true;
        if (!preg_match('/<p>/i', $str)) {
            //没有匹配到p标签
            $strArr = explode(PHP_EOL, $str);
            $str = '';
            foreach ($strArr as $v) {
                $v = myTrim($v);
                if (empty($v)) {
                    continue;
                }
                $str .= $this->repPvalue($v, $downloadImg, false);
            }
        } else {
            $startPnum = substr_count($str, '<p>');
            $endPnum = substr_count($str, '</p>');
            if ($startPnum === $endPnum) {
                $isHtmlspecialchars = false;
            }
            $reg = '/<p>(.*)<\/p>/iUs';
            if (preg_match_all($reg, $str, $mat)) {
                foreach ($mat[0] as $v) {
                    $str = str_ireplace($v, $this->repPvalue($v, $downloadImg, $isHtmlspecialchars), $str);
                }
            }
        }
        return $this->afterPaiban($str);
    }

    /**
     * 排版后的内容处理
     * @param string $str
     * @return string
     */
    private function afterPaiban($str)
    {
        $splitStr = '_@#@_';
        $finalStr = '';
        // 替换内容
        $str = preg_replace('/(<br[^>]*>){2,}/i', '<br>', $str);
        $str = preg_replace('/<p><br><\/p>/i', '', $str);
        // p 标签外的内容处理
        $reg = '/<p>(.*)<\/p>/iUs';
        $pArr = [];
        if (preg_match_all($reg, $str, $mat)) {
            $pArr = $mat[1];
        }
        $str = preg_replace($reg, $splitStr, $str);
        $strArr = explode($splitStr, $str);
        if (!empty($strArr)) {
            $curK = 0;
            foreach ($strArr as $v) {
                if (!empty($v)) {
                    $finalStr .= $this->getPBrValue($v);
                } else {
                    if (isset($pArr[$curK])) {
                        $finalStr .= $this->getPBrValue($pArr[$curK]);
                        $curK++;
                    }
                }
            }
        }
        return $finalStr;
    }

    /**
     * 获取br分标签后的内容
     * @param string $pv
     * @return string
     */
    private function getPBrValue($pv)
    {
        $str = '';
        $pv = myTrim($pv);
        if (!empty($pv)) {
            $brArr = explode('<br>', $pv);
            foreach ($brArr as $v) {
                if (empty($v)) {
                    continue;
                }
                $v = myTrim($v);
                if (!empty($v)) {
                    $str .= '<p>' . $v . '</p>';
                }
            }
        }
        return $str;
    }

    /**
     * 获取p标签字段的格式化内容
     * @param string $pv
     * @param bool $isHtmlspecialchars 是否转义内容
     * @return string
     */
    private function getPValue($pv, $isHtmlspecialchars = false)
    {
        $pv = myTrim($pv);
        if ($isHtmlspecialchars) {
            $pv = htmlspecialchars($pv);
        }
        return $pv;
    }

    /**
     * 正则替换p标签内容
     * @param string $pv
     * @param bool $downloadImg
     * @param bool $isHtmlspecialchars
     * @return string
     */
    private function repPvalue($pv, $downloadImg = true, $isHtmlspecialchars = false)
    {
        $pv = str_ireplace('<p>', '', $pv);
        $pv = str_ireplace('</p>', '', $pv);
        $pv = preg_replace('/&[\w]+;/iU', '', $pv);
        $pv = myTrim($pv);
        if (!empty($pv)) {
            if (preg_match('/<img/i', $pv)) {
                if ($downloadImg) {
                    $srcReg = '/src=[\'"]([^\'"]*)[\'"]/iU';
                    if (preg_match_all($srcReg, $pv, $mat)) {
                        foreach ($mat[1] as $v) {
                            if (!preg_match('/' . getDomain() . '/i', $v)) {
                                $srcDir = downloadImg($v);
                                if ($srcDir) {
                                    $pv = str_ireplace($v, $srcDir, $pv);
                                }
                            }
                        }
                    }
                }
                return '<p>' . $pv . '</p>';
            } else {
                return '<p>' . $this->getPValue($pv, $isHtmlspecialchars) . '</p>';
            }
        }
        return '';
    }
}