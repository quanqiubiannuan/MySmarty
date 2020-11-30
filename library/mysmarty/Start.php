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
        define('MYSMARTY_VERSION', '1.0.0');
        define('APPLICATION_DIR', ROOT_DIR . '/application');
        define('EXTEND_DIR', ROOT_DIR . '/extend');
        define('PUBLIC_DIR', ROOT_DIR . '/public');
        define('STATIC_DIR', PUBLIC_DIR . '/static');
        define('UPLOAD_DIR', PUBLIC_DIR . '/upload');
        define('RUNTIME_DIR', ROOT_DIR . '/runtime');
        define('LIBRARY_DIR', ROOT_DIR . '/library');
        define('CONFIG_DIR', ROOT_DIR . '/config');
        define('CONFIG_FILE', RUNTIME_DIR . '/cache/config.php');
        define('ROUTE_FILE', RUNTIME_DIR . '/cache/route.php');
        // 自动加载
        spl_autoload_register(function ($class) {
            require_once ROOT_DIR . '/' . str_ireplace('\\', '/', $class) . '.php';
        });
        // 引入核心函数库
        require_once LIBRARY_DIR . '/function.php';
        require_once APPLICATION_DIR . '/common.php';
        // 生成配置文件
        generateConfig();
        if (!config('app.debug', false)) {
            error_reporting(0);
        } else {
            set_error_handler('errorHandler');
            set_exception_handler('exceptionHandler');
        }
        date_default_timezone_set(config('app.default_timezone'));
        if (!empty(config('app.app_init')) && function_exists(config('app.app_init'))) {
            call_user_func(config('app.app_init'));
        }
        // 加载第三方库
        if (file_exists(ROOT_DIR . '/vendor/autoload.php')) {
            require_once ROOT_DIR . '/vendor/autoload.php';
        }
        // 控制台运行，不需要路由
        if (isCliMode()) {
            Console::start();
            exit();
        }
        // 输出X-Powered-By信息
        if (!empty(config('app.x_powered_by'))) {
            header('X-Powered-By:' . config('app.x_powered_by'));
        }
        // session开启
        if (config('session.status') === 1) {
            startSession();
        }
        // 生成路由
        generateRoute();
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
     * @param string $middleware 中间件的地址
     * @param array $params 路由参数
     */
    private static function checkMiddleware(string $middleware, array $params = []): void
    {
        // 调用中间件方法执行
        $middlewareObj = new $middleware();
        if (false === $middlewareObj->handle($params)) {
            // 失败后执行
            $middlewareObj->fail($params);
            exit();
        }
    }

    /**
     * 路径跳转
     * @param string $uri
     */
    public static function goPath(string $uri): void
    {
        $controller = CONTROLLER;
        $action = ACTION;
        // 方法执行需要的参数
        $params = [];
        var_dump($uri);
        var_dump(ROUTE);
        if (!empty($uri)) {
            foreach (ROUTE as $v) {
                // 匹配当前规则，获取()内的内容
                if (preg_match('#^' . $v['uri'] . '$#iU', $uri, $mat)) {
                    foreach ($v['methodParams'] as $param) {
                        if (isset($mat[$param])) {
                            $params[$param] = $mat[$param];
                        }
                    }
                    // 执行中间件
                    foreach ($v['methodMiddleware'] as $midd) {
                        self::checkMiddleware($midd, $params);
                    }
                    // 执行缓存
                    break;
                }
            }
        }
        echo PHP_EOL;
        var_dump($controller, $action, $params);
        exit();
        self::go(MODULE, $controller, $action, $params);
    }
}