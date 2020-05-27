<?php

namespace library\mysmarty;

/**
 * 字符处理
 *
 * @author 戴记
 *
 */
class Word
{

    private $dictFile = EXTEND_DIR . '/dict/dict.txt';

    private $pinyinFile = EXTEND_DIR . '/dict/pinyin.txt';

    private static $obj;

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
        return self::$obj;
    }

    /**
     * 获取中文的拼音
     *
     * @param string $word
     * @return string
     */
    public function getPinyin($word)
    {
        $handle = fopen($this->pinyinFile, 'r');
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $lineArr = explode('`', trim($line));
                if (count($lineArr) !== 2) {
                    break;
                }
                $word = str_replace($lineArr[0], $lineArr[1] . ' ', $word);
                if (!preg_match('/[\x{4e00}-\x{9fa5}]/u', $word)) {
                    break;
                }
            }
            fclose($handle);
        }
        return trim($word);
    }

    /**
     * 获取原始分词结果
     *
     * @param string $text
     *            分词文本
     * @return array[]
     */
    public function getAnalyseText($text)
    {
        $result = [];
        if (!empty($text)){
            $handle = fopen($this->dictFile, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $lineArr = explode(' ', trim($line));
                    if (count($lineArr) !== 3) {
                        break;
                    }
                    if (preg_match('/' . $lineArr[0] . '/i', $text)) {
                        $result[] = $lineArr;
                    }
                }
                fclose($handle);
            }
        }
        return $result;
    }

    /**
     * 仅获取文字
     *
     * @param string $text
     * @param integer $minNum
     *            最小分词个数
     * @return string[]
     */
    public function getSimpleAnalyseText($text, $minNum = 1)
    {
        $data = $this->getAnalyseText($text);
        $result = [];
        foreach ($data as $k => $v) {
            if (mb_strlen($v[0], 'utf-8') >= $minNum) {
                $result[] = $v[0];
            }
        }
        return array_unique($result);
    }
}