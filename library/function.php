<?php

use library\mysmarty\Cache;
use library\mysmarty\Caiji;
use library\mysmarty\Ckeditor;
use library\mysmarty\Config;
use library\mysmarty\Cookie;
use library\mysmarty\ElasticSearch;
use library\mysmarty\Env;
use library\mysmarty\IpLocation;
use library\mysmarty\Query;
use library\mysmarty\Session;
use library\mysmarty\Sqlite;
use library\mysmarty\Start;

/**
 * 格式化字节单位
 * @param int $size 多少字节
 * @param int $decimals 小数点保留几位
 * @return string
 */
function formatFileSize(int $size, int $decimals = 0): string
{
    $str = '';
    if ($size < 1024) {
        $str = $size . 'bytes';
    } else if ($size < 1048576) {
        $str = int_format($size / 1024, $decimals, '.', '') . 'KB';
    } else if ($size < 1073741824) {
        $str = int_format($size / 1048576, $decimals, '.', '') . 'MB';
    } else if ($size < 1099511627776) {
        $str = int_format($size / 1073741824, $decimals, '.', '') . 'GB';
    } else {
        $str = int_format($size / 1099511627776, $decimals, '.', '') . 'TB';
    }
    return $str;
}

/**
 * 获取当前的语言数组
 * @param string $name
 * @return array|string
 */
function getCurrentLang(string $name = ''): string|array
{
    $data = getRequireOnceData(ROOT_DIR . '/application/' . MODULE . '/lang/' . strtolower(getCurrentBrowserLanguage()) . '.php');
    if (!empty($name)) {
        return $data[$name] ?? '';
    }
    return $data;
}

/**
 * 是否为GET请求
 * @return bool
 */
function isGet(): bool
{
    return getServerValue('REQUEST_METHOD') === 'GET';
}

/**
 * 是否为POST请求
 * @return bool
 */
function isPost(): bool
{
    return getServerValue('REQUEST_METHOD') === 'POST';
}

/**
 * 是否为PUT请求
 * @return bool
 */
function isPut(): bool
{
    return getServerValue('REQUEST_METHOD') === 'PUT';
}

/**
 * 是否为DELTE请求
 * @return bool
 */
function isDelete(): bool
{
    return getServerValue('REQUEST_METHOD') === 'DELETE';
}

/**
 * 是否为HEAD请求
 * @return bool
 */
function isHead(): bool
{
    return getServerValue('REQUEST_METHOD') === 'HEAD';
}

/**
 * 是否为PATCH请求
 * @return bool
 */
function isPatch(): bool
{
    return getServerValue('REQUEST_METHOD') === 'PATCH';
}

/**
 * 是否为OPTIONS请求
 * @return bool
 */
function isOptions(): bool
{
    return getServerValue('REQUEST_METHOD') === 'OPTIONS';
}

/**
 * 判断当前是否为cgi模式
 * @return bool
 */
function isCgiMode(): bool
{
    return strpos(PHP_SAPI, 'cgi') === 0;
}

/**
 * 获取当前分配的php内存，字节
 * 1字节(B) = 8 位(bit)
 * 1 kb = 1024 字节
 * 1 mb = 1024 kb
 * @return int
 */
function getMemoryUsage(): int
{
    return memory_get_usage();
}

/**
 * 获取当前时间，微秒
 * 1 毫秒 = 1000 微秒
 * 1 秒 = 1000 毫秒
 * @return float
 */
function getCurrentMicroTime(): float
{
    list($usec, $sec) = explode(' ', microtime());
    return ((float)$usec + (float)$sec);
}

/**
 * 判断服务器是否是windows操作系统
 * @return bool
 */
function isWin(): bool
{
    if (stripos(PHP_OS, 'WIN') === 0) {
        return true;
    }
    return false;
}

/**
 * 文章内容排版
 * @param string $str
 * @param bool $downloadImg 自动下载内容中的图片
 * @return string
 */
function paiban(string $str, bool $downloadImg = true): string
{
    return Ckeditor::getInstance()->getContent($str, $downloadImg);
}

/**
 * 下载图片
 * @param string $imgSrc
 * @return bool|string
 */
function downloadImg(string $imgSrc): string|bool
{
    if (0 !== stripos($imgSrc, 'http')) {
        return false;
    }
    $hz = '';
    if (preg_match('/\.jpg/i', $imgSrc)) {
        $hz = 'jpg';
    } else if (preg_match('/\.jpeg/i', $imgSrc)) {
        $hz = 'jpeg';
    } else if (preg_match('/\.gif/i', $imgSrc)) {
        $hz = 'gif';
    } else {
        $hz = 'png';
    }
    $pathDir = '/upload/' . date('Ymd');
    $dir = ROOT_DIR . '/public' . $pathDir;
    if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
            return false;
        }
    }
    $filename = md5(time() . $imgSrc) . '.' . $hz;
    $data = Query::getInstance()->setPcUserAgent()
        ->setRandIp()
        ->setUrl($imgSrc)
        ->getOne();
    if (file_put_contents($dir . '/' . $filename, $data)) {
        return $pathDir . '/' . $filename;
    }
    return false;
}

/**
 * 获取当前主域
 * @return string
 */
function getDomain(): string
{
    if (isset($_SERVER['SERVER_NAME'])) {
        return $_SERVER['SERVER_NAME'];
    }
    if (isset($_SERVER['HTTP_HOST'])) {
        return $_SERVER['HTTP_HOST'];
    }
    return 'localhost';
}

/**
 * 获取文章描叙
 * @param string $content
 * @param int $len
 * @return string
 */
function getDescriptionforArticle(string $content, int $len = 200): string
{
    $content = strip_tags(htmlspecialchars_decode($content));
    $content = myTrim($content);
    $content = preg_replace('/([\n]|[\r\n])/', '', $content);
    $content = preg_replace('/[ 　]/u', '', $content);
    $content = mb_substr($content, 0, $len, 'utf-8');
    $content = htmlspecialchars($content);
    return $content;
}

/**
 * 去掉空格
 * @param string $str
 * @return string
 */
function myTrim(string $str): string
{
    $str = trim($str);
    $str = preg_replace('/^[　\s]{1,}/u', '', $str);
    $str = preg_replace('/[　\s]{1,}$/u', '', $str);
    return $str;
}

/**
 * 去掉url中的请求参数
 * @param string $url
 * @return bool|string
 */
function getNoParamUrl(string $url): string|bool
{
    if (false !== strpos($url, '?')) {
        $url = substr($url, 0, strpos($url, '?'));
    }
    if (preg_match('/#/', $url)) {
        $url = substr($url, 0, strpos($url, '#'));
    }
    return $url;
}

/**
 * 获取指定域名下的favicon.ico
 * @param string $domain
 * @return string
 */
function getShortcutUrl(string $domain): string
{
    $url = trim($domain, '/');
    $shortcut = $url . '/favicon.ico';
    if (!isFavicon($shortcut)) {
        $shortcut = '';
        //网页匹配
        $res = Query::getInstance()->setReferer($url)->setUrl($url)->setUserAgent('Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.108 Safari/537.36')
            ->setRandIp()
            ->getOne();
        $reg = '/<link[^>]*href=["\']([^\'"]*)["\'][^>]*>/iU';
        if (preg_match_all($reg, $res, $mat)) {
            foreach ($mat[0] as $k => $v) {
                if (false !== stripos($v, 'icon')) {
                    $shortcut = getUrl($mat[1][$k], $url);
                    break;
                }
            }
        }
    }
    return $shortcut;
}

/**
 * 判断给定的url是否为favicon url
 * @param string $url
 * @return bool bool
 */
function isFavicon(string $url): bool
{
    $res = get_headers($url, 1);
    if (false === strpos($res[0], '200') || false === stripos($res['Content-Type'], 'image')) {
        return false;
    }
    return true;
}

/**
 * 获取绝对url
 * @param string $path 完整网址或没有主域的url
 * @param string $domain 主域名，以http开头
 * @return string
 */
function getUrl(string $path, string $domain): string
{
    if (false !== stripos($path, 'http')) {
        return $path;
    }
    if (preg_match('/^\/\//i', $path)) {
        $path = trim($path, '/');
        $path = substr($path, strpos($path, '/') + 1);
    }
    $path = trim($path, '/');
    $domain = trim($domain, '/');
    return $domain . '/' . $path;
}

/**
 * 输出带有换行符的消息
 * @param string $msg
 */
function echoMsg(string $msg): void
{
    if (isCliMode()) {
        echoCliMsg($msg);
    } else {
        echo $msg . '<br>';
    }
}

/**
 * 在控制台输出一条消息并换行
 * @param string $msg
 */
function echoCliMsg(string $msg): void
{
    echo $msg . PHP_EOL;
}

/**
 * 引入一个文件
 * @param string $file
 */
function requireFile(string $file): void
{
    if (file_exists($file)) {
        require_once $file;
    }
}

/**
 * 引入一个带有返回结果的文件
 * @param string $file
 * @return mixed
 */
function requireReturnFile(string $file): mixed
{
    return getRequireOnceData($file);
}

/**
 * 获取中文字符串
 * @param int $num 多少个
 * @return string
 */
function getZhChar(int $num = 1): string
{
    $char = '';
    for ($i = 0; $i < $num; $i++) {
        $tmp = chr(mt_rand(0xB0, 0xD0)) . chr(mt_rand(0xA1, 0xF0));
        $char .= iconv('GB2312', 'UTF-8', $tmp);
    }
    return $char;
}

/**
 * 获取当前浏览器的语言
 * @return string
 */
function getCurrentBrowserLanguage(): string
{
    $language = getServerValue('HTTP_ACCEPT_LANGUAGE');
    if (!empty($language)) {
        $languageArr = explode(';', $language);
        if (!empty($languageArr)) {
            $languageArr2 = explode(',', $languageArr[0]);
            if (!empty($languageArr2[0])) {
                return $languageArr2[0];
            }
            return $languageArr[0];
        }
        return $language;
    }
    return '';
}

/**
 * 获取body请求的数据
 * @return false|string
 */
function getRequestBodyContent(): string|bool
{
    return file_get_contents('php://input');
}

/**
 * 刷新页面
 * @param string $url 刷新的网址
 * @param int $refreshTime 刷新间隔时间，单位秒
 */
function refresh(string $url = '', int $refreshTime = 1): void
{
    $url = getFixedUrl($url);
    echo '<meta http-equiv="refresh" content="' . $refreshTime . ';url=' . $url . '">';
    exit();
}

/**
 * 是否为控制台模式
 * @return bool
 */
function isCliMode(): bool
{
    return PHP_SAPI === 'cli';
}

/**
 * 获取剩余内存占比
 * @return int 0 - 100
 */
function getMemFreeRate(): int
{
    $data = getMemInfo();
    if (empty($data)) {
        return 0;
    }
    return (int)(100 * $data['MemFree'] / $data['MemTotal']);
}

/**
 * 获取ip所在的位置信息
 *
 * @param string $ip
 * @return string
 */
function getIpLocation(string $ip = ''): string
{
    $location = IpLocation::getInstance()->getlocation($ip);
    return $location['country'] ?? '';
}

/**
 * 设置缓存
 *
 * @param string $name 键
 * @param string $value 值
 * @param int $expire 过期时间
 * @return int
 */
function setCache(string $name, string $value, int $expire = 3600): int
{
    return Cache::set($name, $value, $expire);
}

/**
 * 获取缓存
 * @param string $name 键
 * @param string $defValue 默认值
 * @return string
 */
function getCache(string $name, string $defValue = ''): string
{
    return Cache::get($name, $defValue);
}

/**
 * 删除缓存
 * @param string $name 键
 * @return int
 */
function deleteCache(string $name): int
{
    return Cache::rm($name);
}

/**
 * 获取浏览器useragent
 * @return string
 */
function getUserAgent(): string
{
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * 获取用户的浏览器
 * @param string $userAgent 指定浏览器
 * @return string
 */
function getUserBrowser(string $userAgent = ''): string
{
    $browserStr = '';
    if (empty($userAgent)) {
        $userAgent = getUserAgent();
    }
    if (!empty($userAgent)) {
        if (false !== stripos($userAgent, 'ucweb')) {
            $browserStr = 'UC浏览器';
        } else if (false !== stripos($userAgent, 'qqbrowser')) {
            $browserStr = 'QQ浏览器';
        } else if (false !== stripos($userAgent, '360se')) {
            $browserStr = '360浏览器';
        } else if (false !== stripos($userAgent, 'metasr')) {
            $browserStr = '搜狗浏览器';
        } else if (false !== stripos($userAgent, 'maxthon')) {
            $browserStr = '傲游浏览器';
        } else if (false !== stripos($userAgent, 'chrome')) {
            $browserStr = '谷歌浏览器';
        } else if (false !== stripos($userAgent, 'firefox')) {
            $browserStr = '火狐浏览器';
        } else if (false !== stripos($userAgent, 'opera')) {
            $browserStr = '欧朋浏览器';
        } else if (false !== stripos($userAgent, 'edge')) {
            $browserStr = 'Edge浏览器';
        } else if (false !== stripos($userAgent, 'safari')) {
            $browserStr = 'Safari浏览器';
        } else if (false !== stripos($userAgent, 'msie')) {
            $browserStr = 'IE浏览器';
        } else if (false !== stripos($userAgent, 'Baiduspider')) {
            $browserStr = '百度蜘蛛';
        } else if (false !== stripos($userAgent, 'Googlebot')) {
            $browserStr = '谷歌蜘蛛';
        } else if (false !== stripos($userAgent, '360Spider')) {
            $browserStr = '360蜘蛛';
        } else if (false !== stripos($userAgent, 'Sosospider')) {
            $browserStr = 'SOSO蜘蛛';
        } else if (false !== stripos($userAgent, 'Yahoo!')) {
            $browserStr = '雅虎蜘蛛';
        } else if (false !== stripos($userAgent, 'Youdao')) {
            $browserStr = '有道蜘蛛';
        } else if (false !== stripos($userAgent, 'Sogou')) {
            $browserStr = '搜狗蜘蛛';
        } else if (false !== stripos($userAgent, 'msnbot')) {
            $browserStr = 'MSN蜘蛛';
        } else if (false !== stripos($userAgent, 'bingbot')) {
            $browserStr = '必应蜘蛛';
        } else if (false !== stripos($userAgent, 'YisouSpider')) {
            $browserStr = '神马蜘蛛';
        } else if (false !== stripos($userAgent, 'ia_archiver')) {
            $browserStr = 'Alexa蜘蛛';
        } else if (false !== stripos($userAgent, 'EasouSpider')) {
            $browserStr = '宜搜蜘蛛';
        } else if (false !== stripos($userAgent, 'JikeSpider')) {
            $browserStr = '即刻蜘蛛';
        } else if (false !== stripos($userAgent, 'Spider')) {
            $browserStr = '网页蜘蛛';
        } else {
            $browserStr = '未知';
        }
        if (false !== stripos($userAgent, 'android')) {
            $browserStr .= '（安卓手机）';
        } else if (false !== stripos($userAgent, 'iphone')) {
            $browserStr .= '（苹果手机）';
        } elseif (false !== stripos($userAgent, 'mobile')) {
            $browserStr .= '（手机端）';
        } else {
            $browserStr .= '（电脑端）';
        }
    }
    return $browserStr;
}

/**
 * 获取server值
 *
 * @param string $name
 * @param string $defValue
 * @return string
 */
function getServerValue(string $name, string $defValue = ''): string
{
    return $_SERVER[$name] ?? $defValue;
}

/**
 * 获取环境配置（动态配置）
 *
 * @param string $key
 * @param string $defValue
 * @return mixed
 */
function getLocalEnv(string $key, string $defValue = '')
{
    return Env::get($key, $defValue);
}

/**
 * 格式化js
 *
 * @param string $js
 * @return string
 */
function formatJs(string $js): string
{
    // 替换 /* */
    $js = preg_replace('/\/\*.*\*\//Uis', '', $js);
    $js = preg_replace('/([^:\'"\\\=])\/\/.*([\n]|[\r\n])?/i', '$1', $js);
    // 替换换行
    $js = preg_replace('/([\n]|[\r\n])/', '', $js);
    $js = preg_replace('/[\t]{1,}/', ' ', $js);
    // 替换两个空格及以上空格 为一个
    $js = preg_replace('/[ ]{2,}/', ' ', $js);
    $js = trim($js);
    return $js;
}

/**
 * 格式化css
 *
 * @param string $css
 * @return string
 */
function formatCss(string $css): string
{
    $css = preg_replace('/\/\*.*\*\//Uis', '', $css);
    // 替换换行
    $css = preg_replace('/([\n]|[\r\n])/', '', $css);
    $css = preg_replace('/[\t]{1,}/', ' ', $css);
    // 替换两个空格及以上空格 为一个
    $css = preg_replace('/[ ]{2,}/', ' ', $css);
    return $css;
}

/**
 * 格式化html
 *
 * @param string $html
 * @return string
 */
function formatHtml(string $html): string
{
    $html = preg_replace('/<!--.*-->/Us', '', $html);
    return $html;
}

/**
 * 获取当前模块url
 * @return string
 */
function getModuleUrl(): string
{
    return getAbsoluteUrl() . '/' . Start::$module;
}

/**
 * 获取当前控制器的url
 * @return string
 */
function getControllerUrl(): string
{
    return getModuleUrl() . '/' . toDivideName(Start::$controller);
}

/**
 * 获取当前方法url
 * @return string
 */
function getActionUrl(): string
{
    return getControllerUrl() . '/' . toDivideName(Start::$action);
}

/**
 * 获取内存信息，单位，字节（kb）
 * 仅支持在Linux系统运行
 * @return array
 */
function getMemInfo(): array
{
    $data = [];
    if (getPlatformName() === 'linux') {
        exec('cat /proc/meminfo', $output);
        if (!empty($output)) {
            foreach ($output as $o) {
                $oArr = explode(':', $o);
                $data[trim($oArr[0])] = intval($oArr[1]);
            }
        }
    }
    return $data;
}

/**
 * 获取操作系统平台
 * @return string
 */
function getPlatformName(): string
{
    return strtolower(PHP_OS);
}

/**
 * 获取后台配置项数据
 * @param string $name 数组键名，支持 . 连接的键名
 * @param mixed $defValue 默认值
 * @return string|array
 */
function config(string $name, mixed $defValue = ''): string|array
{
    return Config::getConfig($name, $defValue);
}

/**
 * 500服务端错误
 *
 * @param string $msg 错误信息
 * @param int $code
 */
function error(string $msg, int $code = 503): void
{
    tip($msg, '/', $code);
}

/**
 * 文件未找到
 */
function notFound(): void
{
    http_response_code(404);
    $html = <<<HTML
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>文件未找到</title>
    <style type="text/css">
       *{
            margin:0;
            padding:0;
        }
    </style>
</head>
<body>
    <h5 style="color:red;padding:30px 0 20px;text-align:center;font-size:22px;">文件未找到</h5>
    <p style="text-align:center;">
        <a href='/' title='返回主页'>返回主页</a>
    </p>
</body>
</html>
HTML;
    echo $html;
    exit();
}

/**
 * 重定向
 *
 * @param string $url 跳转网址
 * @param int $code 状态码
 */
function redirect(string $url, int $code = 301): void
{
    $url = getFixedUrl($url);
    header('Location: ' . $url, true, $code);
    exit();
}

/**
 * 获取网站网址
 * @return string
 */
function getAbsoluteUrl(): string
{
    if (defined('URL')) {
        return URL;
    }
    $url = '';
    if (!isCliMode()) {
        $serverPort = (int)getServerValue('SERVER_PORT', 80);
        $url = 'http://';
        if (443 === $serverPort) {
            $url = 'https://';
        }
        $appUrl = \config('app.app_url');
        if (!empty($appUrl)) {
            $url .= $appUrl;
        } else {
            $url .= getServerValue('HTTP_HOST');
        }
    }
    define('URL', $url);
    return $url;
}

/**
 * 提示跳转
 * @param string $msg
 * @param string $url
 * @param int $code
 */
function tip(string $msg, string $url = '', int $code = 200): void
{
    http_response_code($code);
    $color = '#b8daff';
    if ($code >= 400) {
        $color = 'red';
    }
    $url = getFixedUrl($url);
    $html = <<<HTML
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{$msg}</title>
    <style type="text/css">
       *{
            margin:0;
            padding:0;
        }
    </style>
</head>
<body>
    <h5 style="color:{$color};padding:30px 0 20px;text-align:center;font-size:22px;">{$msg}</h5>
    <p style="text-align:center;">
        <a href='{$url}' title='点击跳转'>点击跳转</a>
    </p>
</body>
</html>
HTML;
    echo $html;
    exit();
}

/**
 * 获取POST\GET参数数据
 * @param string $name 字段
 * @param string $defValue 默认值
 * @param bool $trim 是否去掉空格
 * @return string
 */
function input(string $name, string $defValue = '', bool $trim = true): string
{
    if (isset($_POST[$name])) {
        $value = $_POST[$name];
    } else if (isset($_GET[$name])) {
        $value = $_GET[$name];
    } else {
        $value = $defValue;
    }
    if ($trim) {
        $value = trim($value);
    }
    return $value;
}

/**
 * 获取GET请求参数
 * @param string $name 字段
 * @param bool $trim 是否去掉空格
 * @return string
 */
function getString(string $name, bool $trim = true): string
{
    $value = $_GET[$name] ?? '';
    if ($value && $trim) {
        $value = myTrim($value);
    }
    return (string)$value;
}

/**
 * 获取GET请求参数
 * @param string $name 字段
 * @return int
 */
function getInt(string $name): int
{
    $value = $_GET[$name] ?? 0;
    return (int)$value;
}

/**
 * 获取GET请求参数
 * @param string $name 字段
 * @return array
 */
function getAarray(string $name): array
{
    $value = $_GET[$name] ?? [];
    if (!is_array($value)) {
        $value = [];
    }
    return $value;
}

/**
 * 获取POST请求参数
 * @param string $name 字段
 * @param bool $trim 是否去掉空格
 * @return string
 */
function getPostString(string $name, bool $trim = true): string
{
    $value = $_POST[$name] ?? '';
    if ($value && $trim) {
        $value = myTrim($value);
    }
    return (string)$value;
}

/**
 * 获取POST请求参数
 * @param string $name 字段
 * @return int
 */
function getPostInt(string $name): int
{
    $value = $_POST[$name] ?? 0;
    return (int)$value;
}

/**
 * 获取POST请求参数
 * @param string $name 字段
 * @return array
 */
function getPostAarray(string $name): array
{
    $value = $_POST[$name] ?? [];
    if (!is_array($value)) {
        $value = [];
    }
    return $value;
}

/**
 * 获取客户端ip
 * @return string
 */
function getIp(): string
{
    return getServerValue('REMOTE_ADDR');
}

/**
 * 检测是否是合法的IP地址
 * @param string $ip IP地址
 * @param string $type IP地址类型 (ipv4, ipv6)
 * @return boolean
 */
function isValidIp(string $ip, string $type = ''): bool
{
    switch (strtolower($type)) {
        case 'ipv4':
            $flag = FILTER_FLAG_IPV4;
            break;
        case 'ipv6':
            $flag = FILTER_FLAG_IPV6;
            break;
        default:
            $flag = null;
            break;
    }

    return boolval(filter_var($ip, FILTER_VALIDATE_IP, $flag));
}

/**
 * 生成url
 *
 * @param string $path url path部分
 * @return string
 */
function generateUrl(string $path = ''): string
{
    return getFixedUrl($path);
}

/**
 * 获取中文文本分词，基于ElasticSearch
 * 请先安装中文分词器，
 * git网址：https://github.com/medcl/elasticsearch-analysis-ik
 * 安装方法，ElasticSearch bin目录下执行，elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v6.3.2/elasticsearch-analysis-ik-6.3.2.zip
 * @param string $text 待分词文本
 * @param int $minLen
 * @param string $type 需要的分词类别，ENGLISH、CN_WORD
 * @param string $analyzer 分词器，ik_smart、ik_max_word、icu_tokenizer
 * @param int $num 获取多少个分词个数
 * @return mixed
 */
function getAnalyzingText(string $text, int $minLen = 1, string $type = '', string $analyzer = 'ik_smart', int $num = 30)
{
    $text = myTrim(strip_tags($text));
    $result = ElasticSearch::getInstance()->getAnalyze($text, $analyzer);
    $data = [];
    if (!empty($result)) {
        $tokens = $result['tokens'];
        foreach ($tokens as $token) {
            if (mb_strlen($token['token'], 'utf-8') < $minLen) {
                continue;
            }
            if (empty($type)) {
                $data[] = $token['token'];
            } else {
                if (strtolower($type) === strtolower($token['type'])) {
                    $data[] = $token['token'];
                }
            }
        }
        $data = array_unique($data);
        $data = array_slice($data, 0, $num);
    }
    return $data;
}

/**
 * 格式化模块名称，转为小写
 * @param string $module 模块名称
 * @return string
 */
function formatModule(string $module): string
{
    return strtolower($module);
}

/**
 * 格式化控制器名称，转为每个单词首字母大写（将_分隔的小写控制器）
 * @param string $controller 控制器名称
 * @return string
 */
function formatController(string $controller): string
{
    return str_ireplace('_', '', ucwords($controller, '_'));
}

/**
 * 格式化方法
 * @param string $action 转为每个单词首字母大写，第一个字母转为小写（将_分隔的小写方法）
 * @return string
 */
function formatAction(string $action): string
{
    return lcfirst(str_ireplace('_', '', ucwords($action, '_')));
}

/**
 * 检测指定的模块与当前是否一致
 *
 * @param string $module 模块
 * @param string $controller 控制器
 * @param string $action 方法
 * @return boolean
 */
function checkAccess(string $module, string $controller, string $action): bool
{
    $module = formatModule($module);
    $controller = formatController($controller);
    $action = formatAction($action);
    return MODULE === $module && CONTROLLER === $controller && ACTION === $action;
}

/**
 * 输出json数据
 * @param int $status
 * @param array $data
 * @param string $msg
 * @param int $type
 */
function echoJson(int $status = 1, array $data = [], string $msg = '', int $type = JSON_UNESCAPED_UNICODE): void
{
    json([
        'data' => $data,
        'status' => $status,
        'msg' => $msg
    ], $type);
}

/**
 * 输出json数据
 * @param array|string $data
 * @param int $type
 */
function json(string|array $data, int $type = JSON_UNESCAPED_UNICODE): void
{
    header('content-type:text/json;charset=utf-8');
    if (is_array($data)) {
        echo json_encode($data, $type);
    } else {
        echo $data;
    }
    exit();
}

/**
 * 输出跨域json数据
 * @param int $status
 * @param array $data
 * @param string $msg
 * @param int $type
 */
function echoCorsJson(int $status = 1, array $data = [], string $msg = '', int $type = JSON_UNESCAPED_UNICODE): void
{
    $access_control_allow_origin = config('cors.access_control_allow_origin');
    if (!empty($access_control_allow_origin)) {
        header('Access-Control-Allow-Origin:' . $access_control_allow_origin);
    }
    $access_control_allow_credentials = config('cors.access_control_allow_credentials');
    if (!empty($access_control_allow_credentials)) {
        header('Access-Control-Allow-Credentials:' . $access_control_allow_credentials);
    }
    $access_control_allow_methods = config('cors.access_control_allow_methods');
    if (!empty($access_control_allow_methods)) {
        header('Access-Control-Allow-Methods:' . $access_control_allow_methods);
    }
    $access_control_allow_headers = config('cors.access_control_allow_headers');
    if (!empty($access_control_allow_headers)) {
        header('Access-Control-Allow-Headers:' . $access_control_allow_headers);
    }
    $access_control_expose_headers = config('cors.access_control_expose_headers');
    if (!empty($access_control_expose_headers)) {
        header('Access-Control-Expose-Headers:' . $access_control_expose_headers);
    }
    $access_control_max_age = config('cors.access_control_max_age');
    if ($access_control_max_age > 0) {
        header('Access-Control-Max-Age:' . $access_control_max_age);
    }
    json([
        'data' => $data,
        'status' => $status,
        'msg' => $msg
    ]);
}

/**
 * 异常处理
 * @param Error|ErrorException|\http\Exception\RuntimeException $exception
 */
function exceptionHandler(Error|ErrorException|http\Exception\RuntimeException $exception): void
{
    $sep = '<br>';
    if (isCliMode()) {
        $sep = PHP_EOL;
    }
    echo '错误文件: ', $exception->getFile(), $sep;
    echo '错误行号: ', $exception->getLine(), $sep;
    echo '错误代码: ', $exception->getCode(), $sep;
    echo '错误信息: ', $exception->getMessage(), $sep;
    echo '错误路由信息: ', $exception->getTraceAsString(), $sep;
}

/**
 * 错误处理
 *
 * @param int $errno
 * @param string $errstr
 * @param string $errfile
 * @param int $errline
 */
function errorHandler(int $errno, string $errstr, string $errfile, int $errline): void
{
    $data = ['错误文件: ' . $errfile, '错误行号: ' . $errline, '错误信息: ' . $errstr, '错误级别: ' . $errno];
    if (isCliMode()) {
        echo implode(PHP_EOL, $data);
    } else {
        echo implode('<br>', $data);
    }
    exit();
}

/**
 * 将大写分割为_连接的小写字符串，如MyName -> my_name
 *
 * @param string $name
 * @return string
 */
function toDivideName(string $name): string
{
    $name = preg_replace('/([A-Z])/', '_$1', $name);
    $name = strtolower(trim($name, '_'));
    return $name;
}

/**
 * 获取url请求结果数据
 * @param string $url 请求网址
 * @param bool $isMobile 是否模拟手机请求
 * @return string|bool
 */
function getUrlResult(string $url, bool $isMobile = false): string|bool
{
    $caiji = new Caiji($url);
    if (!$isMobile) {
        $agent = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36';
    } else {
        $agent = 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1';
    }
    $caiji->setAgent($agent);
    $caiji->setCookieFile(RUNTIME_DIR . '/cookie.txt');
    $caiji->setIp(mt_rand(1, 255) . '.' . mt_rand(1, 255) . '.' . mt_rand(1, 255) . '.' . mt_rand(1, 255));
    $caiji->setRefer($url);
    $caiji->setTime(3);
    return $caiji->getRes();
}

/**
 * 获取模板配置变量,区分大小写
 * @param string $configFile 模板配置文件名
 * @param string $name 字段名
 * @param string $section 字段所在节点（域）
 * @return boolean|string
 */
function getTempletConfig(string $configFile, string $name, string $section = ''): string|bool
{
    // 判断模板配置文件是否存在
    $file = APPLICATION_DIR . '/' . MODULE . '/config/' . $configFile;
    if (!file_exists($file)) {
        return false;
    }
    $handle = fopen($file, 'rb');
    if (!$handle) {
        return false;
    }
    // 当前模板所在节点
    $isSection = '';
    // 当前值
    $mData = '';
    // 多行查找标记
    $mFlag = false;
    // 一行一行的读取
    while (($str = fgets($handle)) !== false) {
        // 去掉当前行内容的空格
        $str = trim($str);
        if (empty($str)) {
            continue;
        }
        // 截取当前行的第一个字符
        switch ($str[0]) {
            case '#':
                // 注释
                continue 2;
            case '[':
                // 节点
                if (preg_match('/\[(.*)\]/U', $str, $mat)) {
                    // 当前节点
                    $isSection = trim($mat[1]);
                    $isSection = trim($isSection, '.');
                }
                break;
        }
        // 查找的节点与当前节点不一样
        if ($isSection !== $section) {
            continue;
        }
        // 不是多行
        if (!$mFlag) {
            // 分隔当前行内容
            $arr = explode('=', $str);
            if (count($arr) !== 2) {
                continue;
            }
            // 字段是否相等
            $key = trim($arr[0]);
            if ($key !== $name) {
                continue;
            }
            // 当前值
            $value = trim($arr[1]);
        } else {
            // 多行，当前值就等于当前行内容
            $value = $str;
        }
        if (strpos($value, '"""') === 0) {
            // 多行
            $mData = substr($value, 3) . PHP_EOL;
            $mFlag = true;
        } else if (substr($value, -3) === '"""') {
            // 多行结束
            $mData .= substr($value, 0, -3);
            $mFlag = false;
            break;
        } else {
            if (!$mFlag) {
                // 不是多行，直接返回当前值
                $mData = $value;
                break;
            }
            // 是多行，就拼接数据
            $mData .= $value . PHP_EOL;
        }
    }
    fclose($handle);
    return trim($mData, PHP_EOL);
}

/**
 * 效验邮箱是否正确
 * @param string $email 电子邮箱
 * @return boolean
 */
function isEmail(string $email): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

/**
 * 验证手机号
 * @param string $phone 手机号
 * @return boolean
 */
function isPhone(string $phone): bool
{
    if (!preg_match('/^1[\d]{10}$/U', $phone)) {
        return false;
    }
    return true;
}

/**
 * 验证url
 * @param string $url 网址
 * @return boolean
 */
function isUrl(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    return true;
}

/**
 * ip是否有效
 * @param string $ip
 * @return bool
 */
function isIp(string $ip): bool
{
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }
    return true;
}

/**
 * 判断是否是主域
 * @param string $domain 主域
 * @return boolean
 */
function isDomain(string $domain): bool
{
    if (preg_match('/com.cn/i', $domain)) {
        if (!preg_match('/^[\w]+\.com\.cn$/Ui', $domain)) {
            return false;
        }
    } else {
        if (!preg_match('/^[\w]+\.[\w]+$/Ui', $domain)) {
            return false;
        }
    }
    return true;
}

/**
 * 判断当前是不是手机端
 * @return boolean
 */
function isMobile(): bool
{
    if (preg_match('/mobile|android|iphone/i', getServerValue('HTTP_USER_AGENT'))) {
        return true;
    }
    return false;
}

/**
 * 获取修正后的url
 * @param string $url
 * @return string
 */
function getFixedUrl(string $url): string
{
    if (empty($url)) {
        $url = getAbsoluteUrl();
    } else if (false === stripos($url, 'http')) {
        $url = getAbsoluteUrl() . '/' . trim($url, '/');
    }
    return $url;
}

/**
 * 获取当前模块路由规则
 * @return array
 */
function getCurrentRoute(): array
{
    return getRequireOnceData(ROOT_DIR . '/application/' . MODULE . '/route.php');
}

/**
 * 返回php文件定义的数组
 * @param string $phpFile
 * @return mixed
 */
function getRequireOnceData(string $phpFile)
{
    $data = [];
    $phpFileMd5 = strtoupper(md5($phpFile));
    if (!defined($phpFileMd5)) {
        if (file_exists($phpFile)) {
            $data = require $phpFile;
            define($phpFileMd5, $data);
        }
    } else {
        $data = constant($phpFileMd5);
    }
    return $data;
}

/**
 * 获取session值
 *
 * @param string $name
 * @return mixed
 */
function getSession(string $name)
{
    return Session::getInstance()->get($name, false);
}

/**
 * 设置session
 *
 * @param string $name
 * @param string $value
 */
function setSession(string $name, string $value): void
{
    Session::getInstance()->set($name, $value);
}

/**
 * 删除session
 * @param string $name
 */
function deleteSession(string $name): void
{
    Session::getInstance()->delete($name);
}

/**
 * 开启session
 */
function startSession(): void
{
    Session::getInstance()->startSession();
}

/**
 * 删除所有session
 */
function clearAllSession(): void
{
    Session::getInstance()->clear();
}

/**
 * 获取cookie值
 * @param string $name
 * @return string
 */
function getLocalCookie(string $name): string
{
    return Cookie::getInstance()->get($name, false);
}

/**
 * 设置cookie
 * @param string $name
 * @param string $value
 */
function setLocalCookie(string $name, string $value): void
{
    Cookie::getInstance()->set($name, $value);
}

/**
 * 删除cookie
 * @param string $name
 */
function deleteCookie(string $name): void
{
    Cookie::getInstance()->delete($name);
}

/**
 * 清除所有cookie
 */
function clearAllCookie(): void
{
    Cookie::getInstance()->clear();
}

/**
 * 格式化时间
 * @param int $time 时间，单位秒
 * @return string
 */
function formatTime(int $time): string
{
    $cha = time() - $time;
    if ($cha === 0) {
        return '刚刚';
    }
    $unit = '前';
    if ($cha < 0) {
        $cha *= -1;
        $unit = '后';
    }
    if ($cha < 60) {
        return $cha . '秒' . $unit;
    } else if ($cha < 3600) {
        return (int)($cha / 60) . '分钟' . $unit;
    } else if ($cha < 86400) {
        return (int)($cha / 3600) . '小时' . $unit;
    } else {
        return (int)($cha / 86400) . '天' . $unit;
    }
}

/**
 * 查询手机号归属地
 * @param string $phone
 * @return mixed
 */
function getPhoneAddress(string $phone)
{
    $phone = substr($phone, 0, 7);
    $res = Sqlite::getInstance()->setDatabaseDir(EXTEND_DIR . '/phonedb/')
        ->name('phone')
        ->table('phones')
        ->field('phones.type,regions.province,regions.city,regions.zip_code,regions.area_code')
        ->eq('int', $phone)
        ->join('regions', 'phones.region_id=regions.id')
        ->find();
    if (empty($res)) {
        return false;
    }
    switch ($res['type']) {
        case 1:
            $res['type'] = '中国移动';
            break;
        case 2:
            $res['type'] = '中国联通';
            break;
        case 3:
            $res['type'] = '中国电信';
            break;
        case 4:
            $res['type'] = '中国电信虚拟运营商';
            break;
        case 5:
            $res['type'] = '中国联通虚拟运营商';
            break;
        case 6:
            $res['type'] = '中国移动虚拟运营商';
            break;
    }
    return $res;
}

/**
 * 将xml结构转为数组
 * @param string $xml
 * @return array
 */
function xmlToArray(string $xml): array
{
    try {
        $xml = preg_replace('/<!\[CDATA\[(.*)\]\]>/isU', '$1', $xml);
        return json_decode(json_encode(simplexml_load_string($xml)), true);
    } catch (\Exception $e) {
        return [];
    }
}

/**
 * 将数组转为标准的xml结构
 * @param array $data
 * @return mixed
 */
function arrayToXml(array $data)
{
    $xml = arrayToXmlStr($data);
    if (empty($xml)) {
        return false;
    }
    $xmlObj = new SimpleXMLElement($xml);
    return $xmlObj->asXML();
}

/**
 * 将数组转为xml字符串
 * @param array $data
 * @return string
 */
function arrayToXmlStr(array $data): string
{
    if (!is_array($data)) {
        return '';
    }
    $xml = '';
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $xml .= '<' . $k . '>' . arrayToXmlStr($v) . '</' . $k . '>';
        } else {
            $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
        }
    }
    return $xml;
}

/**
 * 判断字符串是否为中文字符
 * @param string $str
 * @return bool
 */
function isZh(string $str): bool
{
    if (!preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $str)) {
        return false;
    }
    return true;
}

/**
 * 获取框架版本号
 * @return string
 */
function getMySmartyVersion(): string
{
    return MYSMARTY_VERSION;
}

/**
 * 是否是代理ip
 * @return bool
 */
function isProxyIp(): bool
{
    if (!empty(getServerValue('HTTP_VIA'))) {
        return true;
    }
    return false;
}

/**
 * 通过给定的文件创建目录
 * @param string $file 文件路径
 * @return bool
 */
function createDirByFile(string $file): bool
{
    if (file_exists($file)) {
        return true;
    }
    $path = pathinfo($file);
    return createDir($path['dirname']);
}

/**
 * 创建文件夹
 * @param string $dir
 * @return bool
 */
function createDir(string $dir): bool
{
    if (!is_dir($dir)) {
        return mkdir($dir, 0777, true);
    }
    return true;
}

/**
 * 输出html响应头
 */
function echoHtmlHeader(): void
{
    header('content-type:text/html;charset=utf-8');
    header('X-Powered-By:' . config('app.x_powered_by'));
}