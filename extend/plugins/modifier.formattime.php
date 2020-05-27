<?php

/**
 * 格式化时间
 * @param string $time 时间戳或时间格式
 * @param string $format Y-m-d H:i:s
 * @return string
 */
function smarty_modifier_formattime($time, $format = 'Y-m-d H:i:s')
{
    if (!is_int($time)) {
        $time = strtotime($time);
    }
    return date($format, $time);
}