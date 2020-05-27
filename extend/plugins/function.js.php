<?php

/**
 * js自动合并,多个逗号分隔
 * {js href='static/js/jquery-3.3.1.slim.min.js,static/js/popper.min.js,static/js/bootstrap.min.js' format='1'}
 *
 * @param array $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_js($params, Smarty_Internal_Template $template)
{
    $js = '';
    $format = $params['format'] ?? 1;
    $href = $params['href'];
    $hrefs = explode(',', $href);
    if (!$format) {
        foreach ($hrefs as $h) {
            if (!preg_match('/^http/', $h)) {
                $h = '/' . trim($h, '/');
                $h = getAbsoluteUrl() . $h;
            }
            $js .= '<script src="' . $h . '"></script>';
        }
    } else {
        $dir = PUBLIC_DIR . '/runtime';
        $md5 = md5($href) . '.js';
        $file = $dir . '/' . $md5;
        if (config('app.debug', false) || !file_exists($file)) {
            $jsData = '';
            foreach ($hrefs as $v) {
                if (!preg_match('/^http/', $v)) {
                    $v = '/' . trim($v, '/');
                    if (!file_exists(PUBLIC_DIR . $v)) {
                        continue;
                    }
                    $tmp = file_get_contents(PUBLIC_DIR . $v);
                } else {
                    $tmp = file_get_contents($v);
                }
                $jsData .= formatJs($tmp);
            }
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            file_put_contents($file, $jsData);
        }
        $url = getAbsoluteUrl() . '/runtime/' . $md5;
        $js = '<script src="' . $url . '"></script>';
    }
    return $js;
}