<?php
/**
 * 截取字符串
 * @param string $str 原字符
 * @param int $len 截取长度
 * @return string|string[]|null
 */
function smarty_modifier_len($str, $len = 30)
{
    $str = strip_tags($str);
    $str = preg_replace('/^[\s　]+/', '', $str);
    if (mb_strlen($str,'utf-8') < $len){
        return $str;
    }

    return mb_substr($str,0,$len).'...';
}