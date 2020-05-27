<?php

namespace library\mysmarty;

/**
 * 限制请求方式
 *
 * @author daiji
 *
 */
class Method
{

    /**
     * 向指定资源提交数据进行处理请求（例如提交表单或者上传文件）。数据被包含在请求体中。POST请求可能会导致新的资源的建立和/或已有资源的修改。
     */
    public static function post()
    {
        self::check('post');
    }

    /**
     * 请求指定的页面信息，并返回实体主体。
     */
    public static function get()
    {
        self::check('get');
    }

    /**
     * 类似于get请求，只不过返回的响应中没有具体的内容，用于获取报头
     */
    public static function head()
    {
        self::check('head');
    }

    /**
     * 从客户端向服务器传送的数据取代指定的文档的内容。
     */
    public static function put()
    {
        self::check('put');
    }

    /**
     * 请求服务器删除指定的页面。
     */
    public static function delete()
    {
        self::check('delete');
    }

    /**
     * HTTP/1.1协议中预留给能够将连接改为管道方式的代理服务器。
     */
    public static function connect()
    {
        self::check('connect');
    }

    /**
     * 允许客户端查看服务器的性能。
     */
    public static function options()
    {
        self::check('options');
    }

    /**
     * 回显服务器收到的请求，主要用于测试或诊断。
     */
    public function trace()
    {
        self::check('trace');
    }

    /**
     * 检测请求方式
     *
     * @param string|array $methods
     *            请求方式,数组或逗号分隔字符串
     * @throws
     */
    public static function check($methods)
    {
        if (!is_array($methods)) {
            $methods = explode(',', $methods);
        }
        $methods = array_map('strtoupper', $methods);
        if (!in_array($_SERVER['REQUEST_METHOD'], $methods, true)) {
            throw new \RuntimeException('不支持当前请求方式：' . $_SERVER['REQUEST_METHOD']);
        }
    }
}