<?php

namespace library\mysmarty;
/**
 * 应用启动类
 * @package library\mysmarty
 */
class Start
{
    //当前模块
    public static string $module;
    //当前控制器
    public static string $controller;
    //当前执行方法
    public static string $action;

    /**
     * 初始化引入
     */
    public static function initCommon(): void
    {
        define('MYSMARTY_VERSION', '0.1.1');
        define('APPLICATION_DIR', ROOT_DIR . '/application');
        define('EXTEND_DIR', ROOT_DIR . '/extend');
        define('PUBLIC_DIR', ROOT_DIR . '/public');
        define('STATIC_DIR', PUBLIC_DIR . '/static');
        define('UPLOAD_DIR', PUBLIC_DIR . '/upload');
        define('RUNTIME_DIR', ROOT_DIR . '/runtime');
        define('LIBRARY_DIR', ROOT_DIR . '/library');
        define('CONFIG_DIR', ROOT_DIR . '/config');
        // 自动加载
        self::loadClass();
        // 引入核心函数库
        require_once LIBRARY_DIR . '/function.php';
        require_once APPLICATION_DIR . '/common.php';
        if (file_exists(APPLICATION_DIR . '/' . MODULE . '/common.php')) {
            require_once APPLICATION_DIR . '/' . MODULE . '/common.php';
        }
        //初始化配置
        Config::initAllConfig();
        date_default_timezone_set(config('app.default_timezone'));
        if (!config('app.debug')) {
            error_reporting(0);
        } else {
            generateRoute(true);
            set_error_handler('errorHandler');
            set_exception_handler('exceptionHandler');
        }
        if (!empty(config('app.app_init')) && function_exists(config('app.app_init'))) {
            call_user_func(config('app.app_init'));
        }
        if (!empty(config('app.x_powered_by'))) {
            header('X-Powered-By:' . config('app.x_powered_by'));
        }
        // session开启
        if (config('session.status') === 1) {
            startSession();
        }
        //加载第三方库
        if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
            require_once ROOT_DIR . '/vendor/autoload.php';
        }
        if (isCliMode()) {
            Console::start();
            exit();
        }
    }

    /**
     * 执行控制器方法
     */
    public static function forward(): void
    {
        self::initCommon();
        self::goPath(getPath());
    }

    /**
     * 自动加载
     */
    public static function loadClass(): void
    {
        // 加载类文件
        spl_autoload_register(function ($class) {
            require_once ROOT_DIR . '/' . str_ireplace('\\', '/', $class) . '.php';
        });
    }

    /**
     * 调用模块方法
     * @param string $module 模块
     * @param string $controller 控制器
     * @param string $action 方法
     * @param array $params 请求参数
     */
    public static function go(string $module, string $controller, string $action, array $params): void
    {
        self::$module = $module;
        self::$controller = $controller;
        self::$action = $action;
        $controllerNamespace = '\application\\' . $module . '\controller\\' . $controller;
        $obj = new $controllerNamespace();
        if (isset($obj->middleware)) {
            //存在中间件属性
            $middleware = $obj->middleware;
            foreach ($middleware as $k => $v) {
                if (is_string($v)) {
                    $middlewareNamespace = '\application\\' . $module . '\middleware\\' . formatController($v);
                    $middlewareObj = new $middlewareNamespace();
                    self::checkMiddleware($middlewareObj);
                } else {
                    $middlewareNamespace = '\application\\' . $module . '\middleware\\' . formatController($k);
                    $middlewareObj = new $middlewareNamespace();
                    if (isset($v['only'])) {
                        if (in_array($action, $v['only'], true)) {
                            self::checkMiddleware($middlewareObj);
                        }
                        continue;
                    }
                    if (isset($v['except'])) {
                        if (!in_array($action, $v['except'], true)) {
                            self::checkMiddleware($middlewareObj);
                        }
                    }
                }
            }
        }
        call_user_func_array(array(
            $obj,
            $action
        ), array_values($params));
    }


    /**
     * 验证中间件
     * @param $middlewareObj
     */
    private static function checkMiddleware($middlewareObj): void
    {
        if (empty(call_user_func_array(array($middlewareObj, 'handle'), []))) {
            $failResult = call_user_func_array(array($middlewareObj, 'fail'), []);
            if (empty($failResult)) {
                error('未授权访问', 401);
            } else {
                if (is_array($failResult)) {
                    json($failResult);
                } else {
                    echo $failResult;
                }
            }
            exit();
        }
    }

    /**
     * 路径跳转
     * @param string $pathInfo
     */
    public static function goPath(string $pathInfo): void
    {
        $pathArr = [];
        if (empty($pathInfo)) {
            //请求默认的主页
            $pathArr = [MODULE, CONTROLLER, ACTION];
        } else {
            $routeArr = getCurrentRoute();
            if (!empty($routeArr)) {
                foreach ($routeArr as $k => $v) {
                    // 替换正则表达式分割符
                    $k = str_ireplace('#', '\#', trim($k, '/'));
                    // 去掉url中的/
                    $v = trim($v, '/');
                    // 匹配当前规则，获取()内的内容
                    if (preg_match('#^' . $k . '$#iU', $pathInfo, $mat)) {
                        // 匹配到了多少个()
                        $len = count($mat);
                        for ($i = 1; $i < $len; $i++) {
                            // 将匹配到的内容替换到请求跳转url中
                            $v = str_ireplace('$' . $i, $mat[$i], $v);
                        }
                        if (0 === stripos($v, 'http')) {
                            redirect($v);
                        }
                        $pathArr = explode('/', $v);
                        break;
                    }
                }
            }
            if (empty($pathArr)) {
                $pathArr = explode('/', $pathInfo);
            }
        }
        $len = count($pathArr);
        $module = '';
        $controller = '';
        $action = '';
        $params = [];
        if ($len >= 3) {
            $module = formatModule($pathArr[0]);
            if ($module !== MODULE) {
                error('该域名禁止访问此模块');
            }
            $controller = formatController($pathArr[1]);
            $action = formatAction($pathArr[2]);
            $tmp = '';
            for ($i = 3; $i < $len; $i++) {
                if ($i % 2 !== 0) {
                    // 键
                    $tmp = $pathArr[$i];
                } else {
                    // 值
                    $_GET[$tmp] = $pathArr[$i];
                    $params[$tmp] = $pathArr[$i];
                }
            }
        } else {
            error('路径错误');
        }
        self::go($module, $controller, $action, $params);
    }
}