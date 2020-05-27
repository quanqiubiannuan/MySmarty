<?php

/**
 * 获取action目录
 * {action}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_action($params, Smarty_Internal_Template $template)
{
    return getActionUrl();
}