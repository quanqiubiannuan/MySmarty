<?php
/**
 * Date: 2019/5/24
 * Time: 15:37
 */

function smarty_function_cookie($params, Smarty_Internal_Template $template)
{
    $name = $params['name'] ?? '';
    if (empty($name)) {
        return '';
    }
    return getLocalCookie($name);
}