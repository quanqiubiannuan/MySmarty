<?php

/**
 * 获取时间
 * 示例：{time format='Y-m-d'}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_time($params, Smarty_Internal_Template $template)
{
    $format = $params['format'] ?? 'Y-m-d';
    return date($format);
}