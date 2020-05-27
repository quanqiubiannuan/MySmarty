<?php

/**
 * 验证码
 * 示例
 * {captcha src='验证码请求网址'}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_captcha($params, Smarty_Internal_Template $template)
{
    $src = getFixedUrl($params['src'] ?? '');
    $html = '<img src="' . $src . '" alt="验证码" style="cursor: pointer;" title="点击图片切换验证码" onclick="this.src=\'' . $src . '?i=\'+Math.random()+\'\'" />';
    return $html;
}