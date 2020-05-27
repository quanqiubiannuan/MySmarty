<?php

/**
 * 去掉首部空格
 * @param string $content
 * @return string
 */
function smarty_modifier_removespaces($content)
{
    $content = preg_replace('/^[\s　]+/', '', $content);
    return trim($content);
}