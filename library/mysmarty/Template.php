<?php

namespace library\mysmarty;
/**
 * 模板解析
 */
class Template
{
    // 静态对象
    private static ?self $obj = null;
    // 模板目录
    private string $templateDir;
    // 编译目录
    private string $compileDir;
    // 配置目录
    private string $configDir;
    // 配置文件
    private string $configFile = '';
    // 存储分配变量的数组
    private array $data = [];
    // 左分隔符
    private string $leftDelimiter = '{';
    // 右分隔符
    private string $rightDelimiter = '}';
    // 函数合法开始标签
    private array $funStartRegTags = ['include', 'foreach', 'if', 'elseif', 'else', 'php', 'config_load'];
    // 函数合法结束标签
    private array $funEndRegTags = ['foreach', 'if', 'php'];
    // 替换标签
    private array $repRegTags = ['literal'];
    // 是否开启编译检查
    private bool $compileCheck = true;
    // 是否开启强制编译
    private bool $forceCompile = true;
    // 是否开启缓存
    private bool $caching = false;
    // 缓存类型
    private string $cachingType = 'file';

    /**
     * 获取静态操作对象
     * @return Template
     */
    public static function getInstance(): self
    {
        if (self::$obj === null) {
            self::$obj = new self();
            self::$obj->compileCheck = config('mysmarty.compile_check', false);
            self::$obj->forceCompile = config('mysmarty.force_compile', false);
            self::$obj->cachingType = config('mysmarty.caching_type', 'file');
        }
        return self::$obj;
    }

    /**
     * 设置模板目录
     * @param string $templateDir 模板目录
     */
    public function setTemplateDir(string $templateDir): void
    {
        if (!createDir($templateDir)) {
            exit('模板目录不存在或无法创建');
        }
        $this->templateDir = realpath($templateDir);
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
     * 设置编译目录
     * @param string $compileDir 编译目录
     */
    public function setCompileDir(string $compileDir): void
    {
        if (!createDir($compileDir)) {
            exit('编译目录不存在或无法创建');
        }
        $this->compileDir = realpath($compileDir);
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
     * 分配变量值
     * @param string $key 变量key
     * @param mixed $value 变量值
     */
    public function assign(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * 显示模板
     * @param string $template 模板文件
     */
    public function display(string $template): void
    {
        // 编译文件key
        $compileKey = md5($template);
        // 编译文件
        $compileFile = $this->compileDir . '/' . $compileKey . '.php';
        if (!file_exists($compileFile) || $this->forceCompile || ($this->compileCheck && (filemtime($compileFile) < filemtime($this->templateDir . '/' . $template)))) {
            // 强制编译
            $templateData = $this->compile($template);
            file_put_contents($compileFile, $templateData);
        }
        extract($this->data);
        echoHtmlHeader();
        require_once $compileFile;
        exit();
    }

    /**
     * 显示缓存
     */
    public function showCache(): void
    {
        if (!$this->caching) {
            return;
        }
        // 如 FileCache 类中的 showCache方法，没有缓存 返回false
        $cacheData = call_user_func([__NAMESPACE__ . '\\' . ucfirst($this->cachingType) . 'Cache', 'showCache']);
        if (false !== $cacheData) {
            echoHtmlHeader();
            exit($cacheData);
        }
    }

    /**
     * 模板文件编译
     * @param string $template 模板文件
     * @return string
     */
    private function compile(string $template): string
    {
        $templateData = file_get_contents($this->templateDir . '/' . ltrim($template, '/'));
        if ($templateData === false) {
            exit('模板文件不存在');
        }
        return $this->compileStr($templateData);
    }

    /**
     * 编译模板字符串
     * @param string $templateData
     * @return string
     */
    private function compileStr(string $templateData): string
    {
        $blockReg = '/' . $this->leftDelimiter . 'block[\s]+name=([a-z0-9_]+)' . $this->rightDelimiter . '(.*)' . $this->leftDelimiter . '\/block' . $this->rightDelimiter . '/iUs';
        if (preg_match('/' . $this->leftDelimiter . 'extends[\s]+file=[\'"]([^\'"]+)[\'"][\s]*' . $this->rightDelimiter . '/iU', $templateData, $mat)) {
            // 解析模板继承表达式
            $parentTemplateData = file_get_contents($this->templateDir . '/' . ltrim($mat[1], '/'));
            if ($parentTemplateData === false) {
                exit('父模板文件不存在');
            }
            $blockData = [];
            if (preg_match_all($blockReg, $templateData, $mat2)) {
                foreach ($mat2[1] as $k => $v) {
                    $blockData[$v] = $mat2[2][$k];
                }
            }
            $parentTemplateData = preg_replace_callback($blockReg, function ($matchs) use ($blockData) {
                return $this->leftDelimiter . 'block name=' . $matchs[1] . $this->rightDelimiter . ($blockData[$matchs[1]] ?? '') . $this->leftDelimiter . '/block' . $this->rightDelimiter;
            }, $parentTemplateData);
            return $this->compileStr($parentTemplateData);
        }
        // 解析普通表达式
        // 去掉block标签
        $templateData = preg_replace($blockReg, '\2', $templateData);
        // 替换标签
        $repData = [];
        $repRegStr = implode('|', $this->repRegTags);
        $templateData = preg_replace_callback('/' . $this->leftDelimiter . '(' . $repRegStr . ')[\s]*([^' . $this->rightDelimiter . ']+)?[\s]*' . $this->rightDelimiter . '(.*)' . $this->leftDelimiter . '\/\1' . $this->rightDelimiter . '/iUs', function ($matchs) use (&$repData) {
            $funCode = '';
            switch ($matchs[1]) {
                case 'literal':
                    $key = 'literal_' . md5('literal' . time() . mt_rand(1000, 9999));
                    $repData[$key] = $matchs[3];
                    $funCode .= $key;
                    break;
            }
            return $funCode;
        }, $templateData);
        // 处理foreach等函数的开始标签
        $funStartRegStr = implode('|', $this->funStartRegTags);
        $funStartReg = '/' . $this->leftDelimiter . '(' . $funStartRegStr . ')[\s]*([^' . $this->rightDelimiter . ']+)?[\s]*' . $this->rightDelimiter . '/is';
        $templateData = preg_replace_callback($funStartReg, function ($matchs) {
            $funCode = '';
            switch ($matchs[1]) {
                case 'foreach':
                    // foreach 标签处理
                    // 先判断是不是php语法
                    if (preg_match('/[\s]+as[\s]+/iU', $matchs[2])) {
                        // 使用的是php语法
                        $funCode .= '<?php foreach(' . $matchs[2] . ') {?>';
                    } else {
                        // 使用的不是php语法
                        $paramData = $this->paramToArr($matchs[2]);
                        $from = $paramData['from'];
                        $item = '$' . ltrim($paramData['item'], '$');
                        $key = '$' . ltrim(($paramData['key'] ?? 'index'), '$');
                        $funCode .= '<?php foreach(' . $from . ' as ' . $item . ' => ' . $key . ') {?>';
                    }
                    break;
                case 'include':
                    // 包含其它模板
                    $paramData = $this->paramToArr($matchs[2], true);
                    $funCode .= $this->compile($paramData['file']);
                    break;
                case 'if':
                    $funCode .= '<?php if(' . $matchs[2] . '){?>';
                    break;
                case 'elseif':
                    $funCode .= '<?php } else if(' . $matchs[2] . '){?>';
                    break;
                case 'else':
                    $funCode .= '<?php } else {?>';
                    break;
                case 'php':
                    $funCode .= '<?php' . PHP_EOL;
                    break;
                case 'config_load':
                    $paramData = $this->paramToArr($matchs[2], true);
                    $this->configFile = $paramData['file'];
                    break;
            }
            return $funCode;
        }, $templateData);
        // 处理foreach等函数的结束标签
        $funEndRegStr = implode('|', $this->funEndRegTags);
        $funEndReg = '/' . $this->leftDelimiter . '\/(' . $funEndRegStr . ')' . $this->rightDelimiter . '/iU';
        $templateData = preg_replace_callback($funEndReg, function ($matchs) {
            $funCode = '';
            switch ($matchs[1]) {
                case 'foreach':
                case 'if':
                    $funCode .= '<?php }?>';
                    break;
                case 'php':
                    $funCode .= PHP_EOL . '?>';
                    break;
            }
            return $funCode;
        }, $templateData);
        // 函数输出
        $reg = '/' . $this->leftDelimiter . '([a-z0-9_]+\([^\)]+\))' . $this->rightDelimiter . '/i';
        $templateData = preg_replace_callback($reg, function ($matchs) {
            return '<?php echo ' . $matchs[1] . ';?>';
        }, $templateData);
        // 输出模板配置变量
        $reg = '/' . $this->leftDelimiter . '#([^\s' . $this->rightDelimiter . '|]+)#' . $this->rightDelimiter . '/i';
        $templateData = preg_replace_callback($reg, function ($matchs) {
            $tmp = explode('.', $matchs[1]);
            switch (count($tmp)) {
                case 1:
                    return '<?php echo getTempletConfig("' . $this->configFile . '","' . $tmp[0] . '");?>';
                case 2:
                    return '<?php echo getTempletConfig("' . $this->configFile . '","' . $tmp[1] . '","' . $tmp[0] . '");?>';
            }
        }, $templateData);
        // 输出变量
        $reg = '/' . $this->leftDelimiter . '(\$[^\s' . $this->rightDelimiter . '|]+)[\s]*(\|[^' . $this->rightDelimiter . ']+)*[\s]*' . $this->rightDelimiter . '/i';
        $templateData = preg_replace_callback($reg, function ($matchs) {
            $len = count($matchs);
            if (2 === $len) {
                return '<?php echo ' . $matchs[1] . ';?>';
            } else if (3 === $len) {
                $formatMethodCode = '';
                $formatMethods = explode('|', $matchs[2]);
                foreach ($formatMethods as $formatMethod) {
                    $formatMethod = trim($formatMethod);
                    if (empty($formatMethod)) {
                        continue;
                    }
                    $formatMethodParams = explode(':', $formatMethod);
                    $paramLen = count($formatMethodParams);
                    $formatMethodParams[0] = trim($formatMethodParams[0]);
                    if (1 == $paramLen) {
                        $formatMethodCode .= '<?php ' . $matchs[1] . ' = call_user_func(\'' . $formatMethodParams[0] . '\', ' . $matchs[1] . ');?>';
                    } else {
                        $paramArr = '[' . $matchs[1];
                        for ($i = 1; $i < $paramLen; $i++) {
                            $paramArr .= ',' . $formatMethodParams[$i];
                        }
                        $paramArr .= ']';
                        $formatMethodCode .= '<?php ' . $matchs[1] . ' = call_user_func_array(\'' . $formatMethodParams[0] . '\',' . $paramArr . ');?>';
                    }
                }
                return $formatMethodCode . '<?php echo ' . $matchs[1] . ';?>';
            }
            return '';
        }, $templateData);
        // 将替换标签的内容替换回来
        if (!empty($repData)) {
            foreach ($repData as $key => $val) {
                $templateData = str_ireplace($key, $val, $templateData);
            }
        }
        // 是否格式化为一行
        if (config('mysmarty.load_output_filter')) {
            $templateData = formatHtml($templateData);
            $templateData = formatJs($templateData);
            $templateData = formatCss($templateData);
            $templateData = myTrim($templateData);
        }
        return $templateData;
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
     * @param string $leftDelimiter 左分隔符
     */
    public function setLeftDelimiter(string $leftDelimiter): void
    {
        $this->leftDelimiter = $leftDelimiter;
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
     * @param string $rightDelimiter 右分隔符
     */
    public function setRightDelimiter(string $rightDelimiter): void
    {
        $this->rightDelimiter = $rightDelimiter;
    }

    /**
     * 将字符串的参数转为数组
     * @param string $param 字符串参数
     * @param bool $trimQm 是否去除字符串上的引号
     * @return array
     */
    private function paramToArr(string $param, bool $trimQm = false): array
    {
        $data = [];
        $param = trim($param);
        $paramArr = preg_split('/[\s]+/', $param);
        foreach ($paramArr as $v) {
            $v = trim($v);
            $vArr = explode('=', $v);
            if (2 === count($vArr)) {
                $val = $vArr[1];
                if ($trimQm) {
                    $val = preg_replace('/[\'"]/', '', $val);
                }
                $data[$vArr[0]] = $val;
            }
        }
        return $data;
    }

    /**
     * 获取是否开启缓存
     * @return bool
     */
    public function getCaching(): bool
    {
        return $this->caching;
    }

    /**
     * 设置缓存是否开启
     * @param bool $caching
     */
    public function setCaching(bool $caching): void
    {
        $this->caching = $caching;
    }

    /**
     * 获取配置目录
     * @return string
     */
    public function getConfigDir(): string
    {
        return $this->configDir;
    }

    /**
     * 设置配置目录
     * @param string $configDir
     */
    public function setConfigDir(string $configDir): void
    {
        $this->configDir = $configDir;
    }
}