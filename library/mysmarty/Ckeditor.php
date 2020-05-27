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
    private $allowTags = '<p><img><h2><h3><h4><strong><i><a><ul><li><ol><blockquote><table><thead><tbody><tr><th><td>';

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
                $content = preg_replace('/<img [^>]*(src=[\'"][^\'"]+[\'"])[^>]*>/iU', '<img $1>', $content);
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
                $str .= '<p>' . $this->getPValue($v, $isHtmlspecialchars) . '</p>';
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
                    if (preg_match($srcReg, $pv, $mat)) {
                        if (!preg_match('/' . getDomain() . '/i', $mat[1])) {
                            $srcDir = downloadImg($mat[1]);
                            if ($srcDir) {
                                $pv = preg_replace($srcReg, 'src="' . $srcDir . '"', $pv);
                            }
                        }
                    }
                }
                if (preg_match('/(<img[^>]+>)/i', $pv, $mat)) {
                    return '<p>' . $mat[1] . '</p>';
                }
            } else {
                return '<p>' . $this->getPValue($pv, $isHtmlspecialchars) . '</p>';
            }
        }
        return '';
    }
}