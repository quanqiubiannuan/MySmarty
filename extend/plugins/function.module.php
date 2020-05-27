<?php

/**
 * 获取module目录
 * {module}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_module($params, Smarty_Internal_Template $template)
{
    return getModuleUrl();
}