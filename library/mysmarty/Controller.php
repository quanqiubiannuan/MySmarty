<?php
/**
 * Date: 2019/3/2
 * Time: 15:34
 */

namespace library\mysmarty;

use Exception;
use Smarty;

require_once ROOT_DIR . '/library/smarty/Smarty.class.php';

class Controller extends Smarty
{

    private $myTemplate;

    private $myCacheId;

    protected $myCache = true;

    /**
     * 构造方法
     * Controller constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // 初始化变量
        $this->myTemplate = $this->getMyTemplate();
        $this->myCacheId = $this->getMyCacheId();
        $this->initSmarty();
    }

    /**
     * 初始化smarty
     */
    final private function initSmarty()
    {
        // 插件目录
        $pluginsDir = ROOT_DIR . '/extend/plugins';
        $this->addPluginsDir($pluginsDir);
        // 模板文件目录
        $templateDir = ROOT_DIR . '/application/' . Start::$module . '/view/';
        $this->setTemplateDir($templateDir);
        // 编译文件目录
        $compileDir = ROOT_DIR . '/runtime/templates_c/' . Start::$module . '/' . strtolower(Start::$controller);
        $this->setCompileDir($compileDir);
        // 配置目录
        $configDir = ROOT_DIR . '/application/' . Start::$module . '/config/';
        if (file_exists($configDir)) {
            $this->setConfigDir($configDir);
        }
        // 编译检查
        $this->setCompileCheck(config('smarty.compile_check', false));
        // 强制编译
        $this->setForceCompile(config('smarty.force_compile', false));
        // 过滤器
        $outputFilter = config('smarty.load_output_filter');
        if ($outputFilter) {
            try {
                $this->loadFilter('pre', 'removecomments');
                $this->loadFilter('output', 'formathtml');
            } catch (Exception $e) {

            }
        }
        // 配置模板分隔标签符
        if (!empty(config('smarty.taglib_begin'))) {
            $this->setLeftDelimiter(config('smarty.taglib_begin'));
        }
        if (!empty(config('smarty.taglib_end'))) {
            $this->setRightDelimiter(config('smarty.taglib_end'));
        }
        // 缓存配置
        $cache = $this->myCache ? config('smarty.cache', 0) : 0;
        if ($cache > 0) {
            $this->setCaching($cache);
            $this->setCacheLifetime(config('smarty.cache_life_time', 3600));
            if (!empty(config('smarty.caching_type', ''))) {
                $this->setCachingType(config('smarty.caching_type', ''));
            } else {
                // 缓存文件目录
                $cacheDir = ROOT_DIR . '/runtime/cache/' . Start::$module . '/' . strtolower(Start::$controller);
                $this->setCacheDir($cacheDir);
            }
        }
    }

    /**
     * 显示模板
     *
     * @param null $template
     * @param null $cache_id
     * @param null $compile_id
     * @param null $parent
     * @throws
     */
    final public function display($template = null, $cache_id = null, $compile_id = null, $parent = null)
    {
        if (empty($template)) {
            $template = $this->myTemplate;
        } else {
            if (!preg_match('#/#', $template)) {
                $tmp = toDivideName(Start::$controller) . '/' . $template;
                if (file_exists(APPLICATION_DIR . '/' . formatModule(Start::$module) . '/view/' . $tmp)) {
                    $template = $tmp;
                }
            }
        }
        if (empty($cache_id)) {
            $cache_id = $this->myCacheId;
        }
        parent::display($template, $cache_id, $compile_id, $parent);
    }

    /**
     * 返回自动生成的模板文件
     *
     * @return string
     */
    final public function getMyTemplate()
    {
        return toDivideName(Start::$controller) . '/' . toDivideName(Start::$action) . '.' . config('smarty.suffix');
    }

    /**
     * 生成唯一的cache_id
     *
     * @return string
     */
    final public function getMyCacheId()
    {
        return md5(getServerValue('QUERY_STRING') ?: '/') . getCurrentBrowserLanguage();
    }

    /**
     * 提示信息
     *
     * @param string $message
     *            提示的文字
     * @param string $url
     *            跳转的url
     * @param integer $status
     *            状态
     * @param int $second
     *            多少秒自动跳转，-1 不自动跳转，0 立即跳转 ，大于0 则多少秒 自动跳转
     * @throws
     */
    final public function sysecho($message, $url, $status = 200, $second = -1)
    {
        http_response_code($status);
        if ($this->getLeftDelimiter() !== '{') {
            $this->setLeftDelimiter('{');
        }
        if ($this->getRightDelimiter() !== '}') {
            $this->setRightDelimiter('}');
        }
        $this->addTemplateDir(LIBRARY_DIR . '/tpl');
        $this->assign('message', $message);
        if (!empty($url)) {
            $url = getFixedUrl($url);
        } else {
            $url = 'javascript:history.go(-1);';
        }
        $this->assign('url', $url);
        $this->assign('second', $second);
        $this->display('_sysecho.html');
        exitApp();
    }

    /**
     * 成功提示
     *
     * @param string $message
     * @param string $url
     */
    final public function success($message, $url = '')
    {
        $this->sysecho($message, $url, 200);
    }

    /**
     * 错误提示
     *
     * @param string $message
     * @param string $url
     */
    final public function error($message, $url = '')
    {
        $this->sysecho($message, $url, 200);
    }

    /**
     * 页面未找到
     */
    final public function notFound()
    {
        $this->sysecho('页面未找到', '', 404);
    }

    /**
     * 服务器错误
     */
    final public function systemError()
    {
        $this->sysecho('服务器错误', '', 503);
    }

    /**
     * 重定向
     *
     * @param string $path
     * @param integer $code
     */
    final public function redirect($path, $code = 302)
    {
        redirect($path, $code);
    }

    /**
     * 渲染模板
     *
     * @param string $template
     *            模板文件
     * @param array $data
     *            分配数据
     */
    final public function view($template = '', $data = [])
    {
        if (!empty($template)) {
            $template = str_ireplace('.', '/', $template);
            $template .= '.' . config('smarty.suffix');
        }
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->assign($k, $v);
            }
        }
        $this->display($template);
    }

    /**
     * 跳转url
     * @param string $url url不可以有pathinfo模式的传参
     * @param array $params 传递的参数，键值对
     */
    final public function dispatch($url, $params = [])
    {
        $paramsStr = '';
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $paramsStr .= '/' . $k . '/' . $v;
            }
        }
        if (0 !== stripos($url, 'http')) {
            $url = trim($url, '/');
            switch (count(explode('/', $url))) {
                case 1:
                    $url = formatModule(Start::$module) . '/' . toDivideName(Start::$controller) . '/' . $url;
                    break;
                case 2:
                    $url = formatModule(Start::$module) . '/' . $url;
                    break;
            }
        }
        redirect($url . $paramsStr);
    }

    /**
     * 判断当前模板是否有缓存
     * @param string $cacheId
     * @return bool
     * @throws
     */
    final public function hasCached($cacheId = '')
    {
        if (empty($cacheId)) {
            $cacheId = $this->myCacheId;
        }
        return $this->isCached($this->myTemplate, $cacheId);
    }

    /**
     * 显示缓存网页
     * @param string $cacheId
     */
    final public function showCached($cacheId = '')
    {
        $this->display(null, $cacheId);
    }

    /**
     * 删除模板缓存
     * @param string $cacheId
     */
    final public function removeCached($cacheId = '')
    {
        if (empty($cacheId)) {
            $cacheId = $this->myCacheId;
        }
        $this->clearCache($this->myTemplate, $cacheId);
    }

    /**
     * 显示缓存模板
     * @param array $data
     */
    final public function cacheDisplay($data = [])
    {
        if (!empty($data)) {
            foreach ($data as $k => $v) {
                $this->assign($k, $v);
            }
        }
        $this->display();
    }
}