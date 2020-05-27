参考链接：<https://www.php.net/manual/zh/features.commandline.webserver.php>

PHP 5.4.0起， CLI SAPI 提供了一个内置的Web服务器。

这个内置的Web服务器主要用于本地开发使用，不可用于线上产品环境。

URI请求会被发送到PHP所在的的工作目录（Working Directory）进行处理，除非你使用了-t参数来自定义不同的目录。

如果请求未指定执行哪个PHP文件，则默认执行目录内的index.php 或者 index.html。如果这两个文件都不存在，服务器会返回404错误。

当你在命令行启动这个Web Server时，如果指定了一个PHP文件，则这个文件会作为一个“路由”脚本，意味着每次请求都会先执行这个脚本。如果这个脚本返回 FALSE ，那么直接返回请求的文件（例如请求静态文件不作任何处理）。否则会把输出返回到浏览器。

**开始使用**

打开控制台切换到程序根目录

执行  `php mysmarty run`

输出结果

```php
PHP 7.2.9 开发服务器启动于 2019-05-05 15:38:24
运行在 localhost:8080
按 CTRL-C 退出
```

打开浏览器访问：**localhost:8080**

**指定端口**

 `php mysmarty run -s mysmarty -p 8000`

```php
PHP 7.2.9 开发服务器启动于 2019-05-05 15:41:58
运行在 localhost:8000
按 CTRL-C 退出
```

