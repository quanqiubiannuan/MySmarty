<?php

use library\mysmarty\Emoji;

/**
 * 输出表情
 * Date: 2019/5/21
 * Time: 15:12
 */

function smarty_function_emoji($params, Smarty_Internal_Template $template)
{
    $name = $params['name'] ?? '';
    Emoji::echoByName($name);
}