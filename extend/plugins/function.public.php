<?php


/**
 * 获取public目录
 * {public}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_public($params, Smarty_Internal_Template $template)
{
    return getAbsoluteUrl();
}