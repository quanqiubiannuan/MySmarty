<?php


/**
 * 生成url
 * 示例：{url path='/home/index/test'}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_url($params, Smarty_Internal_Template $template)
{
    $path = $params['path'] ?? '';
    return generateUrl($path);
}