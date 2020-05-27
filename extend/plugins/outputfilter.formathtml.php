<?php

/**
 * 将html源代码格式化为一行
 * @param string $output
 * @param Smarty_Internal_Template $template
 * @return mixed
 */
function smarty_outputfilter_formathtml($output, Smarty_Internal_Template $template)
{
    // 替换换行
    $output = preg_replace('/([\n]|[\r\n])/', '', $output);
    $output = preg_replace('/[\t]+/', ' ', $output);
    // 替换两个空格及以上空格 为一个
    $output = preg_replace('/[ ]{2,}/', ' ', $output);
    return $output;
}