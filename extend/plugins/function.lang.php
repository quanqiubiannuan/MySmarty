<?php
/**
 * 多语言函数
 * Date: 2019/5/9
 * Time: 9:25
 */

function smarty_function_lang($params, Smarty_Internal_Template $template)
{
    $name = $params['name'] ?? '';
    if (empty($name)) {
        return '';
    }
    return getCurrentLang($name);
}