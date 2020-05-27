<?php

/**
 * css自动合并,多个逗号分隔
 * {css href='/static/css/bootstrap.min.css,static/css/library.css' format='0'}
 *
 * @param array $params            
 * @param Smarty_Internal_Template $template            
 * @return string
 */
function smarty_function_css($params, Smarty_Internal_Template $template)
{
    $css = '';
    $format = $params['format'] ?? 1;
    $href = $params['href'];
    $hrefs = explode(',', $href);
    if (! $format) {
        foreach ($hrefs as $h) {
            if (! preg_match('/^http/', $h)) {
                $h = '/' . trim($h, '/');
                $h = getAbsoluteUrl() . $h;
            }
            $css .= '<link rel="stylesheet" href="' . $h . '">';
        }
    } else {
        $dir = PUBLIC_DIR . '/runtime';
        $md5 = md5($href) . '.css';
        $file = $dir . '/' . $md5;
        if (config('app.debug', false) || ! file_exists($file)) {
            $cssData = '';
            foreach ($hrefs as $v) {
                if (! preg_match('/^http/', $v)) {
                    $v = '/' . trim($v, '/');
                    if (! file_exists(PUBLIC_DIR . $v)) {
                        continue;
                    }
                    $tmp = file_get_contents(PUBLIC_DIR . $v);
                } else {
                    $tmp = file_get_contents($v);
                }
                $cssData .= formatCss($tmp);
            }
            if (! file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($file, $cssData);
        }
        $url = getAbsoluteUrl() . '/runtime/' . $md5;
        $css = '<link rel="stylesheet" href="' . $url . '">';
    }
    return $css;
}