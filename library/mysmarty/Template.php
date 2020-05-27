<?php

namespace library\mysmarty;
use RuntimeException;

/**
 * 模板引擎类
 * @package library\mysmarty
 */
class Template
{
    private static ?self $obj = null;
    // 模板目录所在文件夹
    protected string $templateDir;
    // 编译文件夹
    protected string $compileDir;
    // 缓存文件夹
    protected string $cacheDir;
    // 左分隔符
    protected string $leftDelimiter;
    // 右分隔符
    protected string $rightDelimiter;
    // 分配变量
    protected bool $forceCompile;
    // 当前模板文件
    protected int $cache;
    // mysmarty保留变量存储
    protected string $cachingType;
    // 是否强制生成缓存文件，即使有缓存
    protected int $cacheLifeTime;
    // 缓存配置，0 关闭，1 开启
    protected bool $formatHtml;
    // 自定义缓存存储方式
    protected BaseCache $cacheObj;
    // 缓存时间
    private array $assignVars = [];
    // 是否格式化输出的html内容
    private string $templateFile;
    // 缓存对象
    private array $mysmarty = [];

    /**
     * 构造方法.
     */
    public function __construct()
    {
        $this->cachingType = config('view.caching_type');
        $this->formatHtml = config('view.load_output_filter');
        $this->cache = config('view.cache');
        $this->rightDelimiter = config('view.taglib_end') ?: '}';
        $this->leftDelimiter = config('view.taglib_begin') ?: '{';
        $this->forceCompile = config('view.force_compile');
        $this->cacheLifeTime = config('view.cache_life_time');
    }

    /**
     * 获取实例
     * @return Template
     */
    public static function getInstance(): Template
    {
        if (!isset(self::$obj) || self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * @return bool
     */
    public function getForceCompile(): bool
    {
        return $this->forceCompile;
    }

    /**
     * @param bool $forceCompile
     * @return Template
     */
    public function setForceCompile(bool $forceCompile): Template
    {
        $this->forceCompile = $forceCompile;
        return $this;
    }

    /**
     * @return int
     */
    public function getCache(): int
    {
        return $this->cache;
    }

    /**
     * @param int $cache
     * @return Template
     */
    public function setCache(int $cache): Template
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * @return string
     */
    public function getCachingType(): string
    {
        return $this->cachingType;
    }

    /**
     * @param string $cachingType
     * @return Template
     */
    public function setCachingType(string $cachingType): Template
    {
        $this->cachingType = $cachingType;
        return $this;
    }

    /**
     * @return string
     */
    public function getCacheLifeTime(): string
    {
        return $this->cacheLifeTime;
    }

    /**
     * @param int $cacheLifeTime
     * @return Template
     */
    public function setCacheLifeTime(int $cacheLifeTime): Template
    {
        $this->cacheLifeTime = $cacheLifeTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function getFormatHtml(): bool
    {
        return $this->formatHtml;
    }

    /**
     * @param bool $formatHtml
     * @return Template
     */
    public function setFormatHtml(bool $formatHtml): Template
    {
        $this->formatHtml = $formatHtml;
        return $this;
    }

    /**
     * 获取缓存目录
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }

    /**
     * 设置缓存目录
     * @param string $cacheDir
     * @return Template
     */
    public function setCacheDir($cacheDir): Template
    {
        $this->cacheDir = $cacheDir;
        if (!file_exists($cacheDir) && !mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
            exitApp(sprintf('缓存目录 "%s" 创建失败', $cacheDir));
        }
        return $this;
    }

    /**
     * 获取左分隔符
     * @return string
     */
    public function getLeftDelimiter(): string
    {
        return $this->leftDelimiter;
    }

    /**
     * 设置左分隔符
     * @param string $leftDelimiter
     * @return Template
     */
    public function setLeftDelimiter(string $leftDelimiter): Template
    {
        $this->leftDelimiter = $leftDelimiter;
        return $this;
    }

    /**
     * 获取右分隔符
     * @return string
     */
    public function getRightDelimiter(): string
    {
        return $this->rightDelimiter;
    }

    /**
     * 设置右分隔符
     * @param string $rightDelimiter
     * @return Template
     */
    public function setRightDelimiter(string $rightDelimiter): Template
    {
        $this->rightDelimiter = $rightDelimiter;
        return $this;
    }

    /**
     * 分配变量
     * @param string|array $key 变量键
     * @param mixed $value 变量值
     */
    public function assign($key, $value = ''): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->assignVars[$k] = $v;
            }
        } else {
            $this->assignVars[$key] = $value;
        }
    }

    /**
     * 显示模板
     * @param null|string $template 模板文件
     * @param null|string $cacheId 缓存id
     */
    public function _display(string $template = null, string $cacheId = null): void
    {
        $cacheStatus = false;
        if ($this->cache === 1 && !empty($cacheId)) {
            $cacheStatus = true;
        }
        if ($cacheStatus) {
            $content = $this->showCache($cacheId);
            if ($content !== false) {
                echo $content;
                exit();
            }
        }
        $this->templateFile = $template;
        // 编译模板
        $parseTemplate = $this->compileTemplate($template);
        // 分配变量
        if (!empty($this->assignVars)) {
            extract($this->assignVars);
        }
        if (file_exists($parseTemplate)) {
            ob_start();
            require_once $parseTemplate;
            $content = ob_get_clean();
            // 格式化输出内容
            if ($this->formatHtml) {
                $content = formatHtml(removecomments($content));
            }
            if ($cacheStatus) {
                $this->saveCache($cacheId, $content);
            }
            echo $content;
            exit();
        }
    }

    /**
     * 显示缓存
     * @param string $cacheId
     * @return mixed
     */
    private function showCache(string $cacheId)
    {
        return $this->getCacheObj()->read($cacheId);
    }

    /**
     * 获取缓存对象
     * @return BaseCache
     */
    public function getCacheObj(): BaseCache
    {
        return $this->cacheObj;
    }

    /**
     * 编译模板
     * @param string $template 模板文件
     * @return string 模板文件所在位置
     */
    private function compileTemplate(string $template): string
    {
        $compileDir = $this->getCompileDir();
        $compileFile = rtrim($compileDir, '/') . '/' . md5($template) . '.php';
        if (!$this->forceCompile && file_exists($compileFile)) {
            return $compileFile;
        }
        $parseTemplate = myTrim($this->parseTemplate($template));
        // 替换原样输出数据
        if (isset($this->mysmarty['literal']) && !empty($this->mysmarty['literal'])) {
            foreach ($this->mysmarty['literal'] as $k => $v) {
                $parseTemplate = str_ireplace('###' . $k . '###', $v, $parseTemplate);
            }
        }
        file_put_contents($compileFile, $parseTemplate);
        return $compileFile;
    }

    /**
     * 获取编译目录
     * @return string
     */
    public function getCompileDir(): string
    {
        return $this->compileDir;
    }

    /**
     * 设置编译目录
     * @param string $compileDir
     * @return Template
     */
    public function setCompileDir($compileDir): Template
    {
        $this->compileDir = $compileDir;
        if (!file_exists($compileDir) && !mkdir($compileDir, 0777, true) && !is_dir($compileDir)) {
            exitApp(sprintf('编译目录 "%s" 创建失败', $compileDir));
        }
        return $this;
    }

    /**
     * 模板文件替换
     * @param string $template 模板文件
     * @return string
     */
    private function parseTemplate(string $template): string
    {
        $templateFile = rtrim($this->getTemplateDir(), '/') . '/' . $template;
        if (!file_exists($templateFile)) {
            exitApp('模板文件不存在');
        }
        return $this->parseTemplateData(file_get_contents($templateFile));
    }

    /**
     * 获取模板目录
     * @return string
     */
    public function getTemplateDir(): string
    {
        return $this->templateDir;
    }

    /**
     * 设置模板目录
     * @param string $templateDir
     * @return Template
     */
    public function setTemplateDir($templateDir): Template
    {
        $this->templateDir = $templateDir;
        return $this;
    }

    /**
     * 解析模板内容
     * @param string $templateData 模板数据
     * @return string
     */
    private function parseTemplateData(string $templateData): string
    {
        if (empty($templateData)) {
            return '';
        }
        $leftDelimiter = preg_quote($this->leftDelimiter);
        $rightDelimiter = preg_quote($this->rightDelimiter);
        /**
         * 模板编译
         */
        $replaceCallbackArray = [
            // 内建函数，闭合
            '/' . $leftDelimiter . '([a-z0-9_]+)([\s]+[^' . $rightDelimiter . ']+)?' . $rightDelimiter . '(.*)' . $leftDelimiter . '\/\1' . $rightDelimiter . '/Uis' => static function ($match) {
                $m = '_' . formatAction($match[1]);
                if (method_exists(self::$obj, $m)) {
                    if (empty($match[2])) {
                        return call_user_func([self::$obj, $m], $match[3]);
                    }
                    return call_user_func([self::$obj, $m], $match[2], self::$obj->parseTemplateData(trim($match[3])));
                }
                return '';
            },
            // 内建函数，非闭合
            '/' . $leftDelimiter . '([a-z0-9_]+)([\s]+[^' . $rightDelimiter . ']+)?' . $rightDelimiter . '/U' => static function ($match) {
                $m = formatAction($match[1]);
                if (empty($match[2])) {
                    if (method_exists(self::$obj, '_' . $m)) {
                        return call_user_func([self::$obj, '_' . $m]);
                    }
                    if (function_exists($m)) {
                        return '<?php echo ' . $m . '();?>';
                    }
                    if (function_exists('_' . $m)) {
                        return '<?php echo _' . $m . '();?>';
                    }
                } else {
                    if (method_exists(self::$obj, '_' . $m)) {
                        return call_user_func([self::$obj, '_' . $m], $match[2]);
                    }
                    $argc = preg_split("/[\s,]+/", trim($match[2]));
                    if (function_exists($m)) {
                        return '<?php echo ' . $m . '(' . implode(',', $argc) . ');?>';
                    }
                    if (function_exists('_' . $m)) {
                        return '<?php echo _' . $m . '(' . implode(',', $argc) . ');?>';
                    }
                }
                return '';
            },
            // 模板配置
            '/' . $leftDelimiter . '#([^$]+)#' . $rightDelimiter . '/U' => static function ($match) {
                if (isset(self::$obj->mysmarty['config'])) {
                    return '<?php echo getTempletConfig(\'' . self::$obj->mysmarty['config']['configFile'] . '\',\'' . $match[1] . '\',\'' . self::$obj->mysmarty['config']['section'] . '\');?>';
                }
                return '';
            },
            // 基本变量
            '/' . $leftDelimiter . '(\$[\w]+)[\s]*(\|.+)?' . $rightDelimiter . '/U' => static function ($match) {
                switch (count($match)) {
                    case 2:
                        // 不是变量调节器
                        return '<?php echo ' . $match[1] . ';?>';
                    case 3:
                        // 变量调节器
                        $matArr = explode('|', trim($match[2], '|'));
                        $phpStr = '<?php';
                        foreach ($matArr as $mat) {
                            $tmp = explode(':', $mat);
                            $parameter = $tmp;
                            $parameter[0] = $match[1];
                            $methodName = formatAction($tmp[0]);
                            if (function_exists('_' . $methodName)) {
                                $methodName = '_' . $methodName;
                            }
                            $phpStr .= ' ' . $match[1] . ' = call_user_func_array(\'' . $methodName . '\', [' . implode(',', $parameter) . ']);';
                        }
                        $phpStr .= ' echo ' . $match[1] . ';?>';
                        return $phpStr;
                }
                return '';
            },
            // 内建变量
            '/' . $leftDelimiter . '\$mysmarty\.([\w]+)\.([\w\.]+)' . $rightDelimiter . '/iU' => static function ($match) {
                return self::$obj->mysmarty[$match[1]][$match[2]] ?? '';
            },
            // 注释
            '/' . $leftDelimiter . '\*[\s]*(.*)[\s]*\*' . $rightDelimiter . '/Us' => static function ($match) {
                return '<!-- ' . $match[1] . ' -->';
            }
        ];
        return preg_replace_callback_array($replaceCallbackArray, $templateData);
    }

    /**
     * 保存缓存
     * @param string $cacheId 缓存key
     * @param string $content 缓存内容
     * @return bool
     */
    private function saveCache(string $cacheId, string $content): bool
    {
        return $this->getCacheObj()->write($cacheId, $content, $this->cacheLifeTime);
    }

    /**
     * 判断是否存在缓存
     * @param string $cacheId
     * @return bool
     */
    public function isCached(string $cacheId): bool
    {
        return $this->getCacheObj()->isCached($cacheId);
    }

    /**
     * 删除缓存
     * @param string $cacheId
     * @return bool
     */
    public function clearCache(string $cacheId): bool
    {
        return $this->getCacheObj()->delete($cacheId);
    }

    /**
     * 清空缓存
     * @return bool
     */
    public function clearAllCache(): bool
    {
        return $this->getCacheObj()->purge();
    }

    private function __clone()
    {
    }

    /**
     * 捕获到的内容存储于变量里
     * @param string $paramStr 参数
     * @param string $content 内容
     * @return string
     */
    private function _capture(string $paramStr, string $content = ''): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $val = $this->getValueByName($paramStr, 'name');
        $this->mysmarty['capture'][$val] = $content;
        return '';
    }

    /**
     * 获取字符串中指定的值
     * @param string $str 字符串
     * @param string $name 名称
     * @param bool $stripQuote 是否去掉双引号、单引号
     * @return string
     */
    private function getValueByName(string $str, string $name, bool $stripQuote = true): string
    {
        if ($stripQuote) {
            $str = preg_replace('/[\'"]/', '', $str);
        }
        if (preg_match('/' . $name . '[\s]*=[\s]*([^\s]+)/i', $str, $mat)) {
            return $mat[1];
        }
        return '';
    }

    /**
     * 加载配置
     * @param string $paramStr 参数
     * @return string
     */
    private function _configLoad(string $paramStr): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $configFile = $this->getValueByName($paramStr, 'file');
        $section = $this->getValueByName($paramStr, 'section');
        $this->mysmarty['config'] = [
            'configFile' => $configFile,
            'section' => $section
        ];
        return '';
    }

    /**
     * foreach 循环
     * @param string $paramStr 参数
     * @param string $content 内容
     * @return string
     */
    private function _foreach(string $paramStr, string $content = ''): string
    {
        if (empty($paramStr)) {
            return '';
        }
        return '<?php foreach(' . trim($paramStr) . '){?>' . $content . '<?php }?>';
    }

    /**
     * for 循环
     * @param string $paramStr 参数
     * @param string $content 内容
     * @return string
     */
    private function _for(string $paramStr, string $content = ''): string
    {
        if (empty($paramStr)) {
            return '';
        }
        return '<?php for(' . trim($paramStr) . '){?>' . $content . '<?php }?>';
    }

    /**
     * 包含文件
     * @param string $paramStr 参数
     * @return string
     */
    private function _include(string $paramStr): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $file = $this->getValueByName($paramStr, 'file');
        $templateFile = realpath(self::$obj->templateDir . '/' . $file);
        if (file_exists($templateFile)) {
            return $this->parseTemplateData(file_get_contents($templateFile));
        }
        return '';
    }

    /**
     * 分配变量
     * @param string $paramStr 参数
     * @return string
     */
    private function _assign(string $paramStr): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $var = $this->getValueByName($paramStr, 'var');
        $value = $this->getValueByName($paramStr, 'value', false);
        return '<?php $' . $var . ' = ' . $value . ';?>';
    }

    /**
     * if条件
     * @param string $paramStr 参数
     * @param string $content 内容
     * @return string
     */
    private function _if(string $paramStr, string $content = ''): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $paramStr = $this->convertChar($paramStr);
        return '<?php if(' . $paramStr . '){?>' . $content . '<?php }?>';
    }

    /**
     * 转换模板的条件语句
     * @param string $char
     * @return string
     */
    private function convertChar(string $char): string
    {
        $char = trim($char);
        $char = preg_replace('/[\s]+eq[\s]+/', ' == ', $char);
        $char = preg_replace('/[\s]+neq[\s]+/', ' != ', $char);
        $char = preg_replace('/[\s]+gt[\s]+/', ' > ', $char);
        $char = preg_replace('/[\s]+egt[\s]+/', ' >= ', $char);
        $char = preg_replace('/[\s]+lt[\s]+/', ' < ', $char);
        $char = preg_replace('/[\s]+elt[\s]+/', ' <= ', $char);
        return $char;
    }

    /**
     * elseif条件
     * @param string $paramStr 参数
     * @return string
     */
    private function _elseif(string $paramStr): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $paramStr = $this->convertChar($paramStr);
        return '<?php } else if(' . $paramStr . '){?>';
    }

    /**
     * 原样输出
     * @param string $content 内容
     * @return string
     */
    private function _literal(string $content = ''): string
    {
        $val = 'literal_' . uniqid('mysmarty', true);
        $this->mysmarty['literal'][$val] = $content;
        return '###' . $val . '###';
    }

    /**
     * 继承文件
     * @param string $paramStr 参数
     * @return string
     */
    private function _extends(string $paramStr): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $file = $this->getValueByName($paramStr, 'file');
        $templateFile = realpath(self::$obj->templateDir . '/' . $file);
        if (file_exists($templateFile)) {
            $templateData = file_get_contents($templateFile);
            if (preg_match_all('/\{block[\s]+name=[\'"]([a-z0-9_]+)[\'"]}(.*)\{\/block}/Uis', $templateData, $mat)) {
                foreach ($mat[1] as $k => $v) {
                    $value = $this->mysmarty['block'][$v] ?? $mat[2][$k];
                    $templateData = str_ireplace($mat[0][$k], $value, $templateData);
                }
            }
            return $this->parseTemplateData($templateData);
        }
        return '';
    }

    /**
     * block
     * @param string $paramStr 参数
     * @param string $content 内容
     * @return string
     */
    private function _block(string $paramStr, string $content = ''): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $name = $this->getValueByName($paramStr, 'name');
        $this->mysmarty['block'][$name] = $content;
        return '';
    }

    /**
     * else条件
     * @return string
     */
    private function _else(): string
    {
        return '<?php } else {?>';
    }

    /**
     * css
     * @param string $paramStr 参数
     * @return string
     */
    private function _css(string $paramStr): string
    {
        if (empty($paramStr)) {
            return '';
        }
        $css = '';
        $format = $this->getValueByName($paramStr, 'format');
        $href = $this->getValueByName($paramStr, 'href');
        $hrefs = explode(',', $href);
        if (!$format) {
            foreach ($hrefs as $h) {
                if (0 !== strpos($h, 'http')) {
                    $h = '/' . trim($h, '/');
                    $h = getAbsoluteUrl() . $h;
                }
                $css .= '<link rel="stylesheet" href="' . $h . '">';
            }
        } else {
            $dir = PUBLIC_DIR . '/runtime';
            $md5 = md5($href) . '.css';
            $file = $dir . '/' . $md5;
            if (config('app.debug', false) || !file_exists($file)) {
                $cssData = '';
                foreach ($hrefs as $v) {
                    if (0 !== strpos($v, 'http')) {
                        $v = '/' . trim($v, '/');
                        if (!file_exists(PUBLIC_DIR . $v)) {
                            continue;
                        }
                        $tmp = file_get_contents(PUBLIC_DIR . $v);
                    } else {
                        $tmp = file_get_contents($v);
                    }
                    $cssData .= formatCss($tmp);
                }
                if (!file_exists($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new RuntimeException(sprintf('目录 "%s" 创建失败', $dir));
                }
                file_put_contents($file, $cssData);
            }
            $url = getAbsoluteUrl() . '/runtime/' . $md5;
            $css = '<link rel="stylesheet" href="' . $url . '">';
        }
        return $css;
    }

    /**
     * js
     * @param string $paramStr 参数
     * @return string
     */
    private function _js(string $paramStr): string
    {
        $js = '';
        $format = $this->getValueByName($paramStr, 'format');
        $href = $this->getValueByName($paramStr, 'href');
        $hrefs = explode(',', $href);
        if (!$format) {
            foreach ($hrefs as $h) {
                if (0 !== strpos0($h, 'http')) {
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
                    if (0 !== strpos($v, 'http')) {
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
                if (!file_exists($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
                    throw new RuntimeException(sprintf('目录 "%s" 创建失败', $dir));
                }
                file_put_contents($file, $jsData);
            }
            $url = getAbsoluteUrl() . '/runtime/' . $md5;
            $js = '<script src="' . $url . '"></script>';
        }
        return $js;
    }
}