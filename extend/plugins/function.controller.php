<?php

/**
 * 获取controller目录
 * {controller}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_controller($params, Smarty_Internal_Template $template)
{
    return getControllerUrl();
}