<?php

/**
 * 在编译之前去掉html源码
 *
 * @param string $source
 * @param Smarty_Internal_Template $template
 * @return mixed
 */
function smarty_prefilter_removecomments($source, Smarty_Internal_Template $template)
{
    // 去掉html注释,<!-- -->
    $source = formatHtml($source);
    // 去掉js注释，/* */
    $source = formatJs($source);
    // 去掉css注释
    $source = formatCss($source);
    return $source;
}