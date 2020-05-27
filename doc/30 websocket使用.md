仅支持Linux操作系统

WebSocket目前仅支持文字传输

**服务端**

创建类继承WebSocket并实现未实现的方法

```php
<?php

namespace application\home\controller;


use library\mysmarty\WebSocket;

class Index extends WebSocket
{

    //绑定ip
    protected $ip = '192.168.241.129';
    //绑定端口
    protected $port = 8888;
    //长度最多为 1024 字节的数据将被接收
    protected $socketRecvLen = 1024;

    /**
     * 握手时的函数
     * @param array $header 客户端握手请求头部数据
     * @return bool true，通过握手，false，拒绝握手
     */
    public function connect($header)
    {
        return true;
    }

    /**
     * 处理接收到的消息
     * @param string $message 客户端发送的消息
     * @return mixed
     */
    public function receive($message)
    {
        var_dump($message);
        $this->sendMessage('ggggg');
    }
}
```

**控制台运行**

`php mysmarty home/test/run`

**客户端**

```html
<html>

<head>
    <meta charset="utf-8">
</head>

<body>

    <input type="text" id="text">

    <button onclick="send();">发送数据</button>
    <script>
        var ws = new WebSocket("ws://192.168.241.129:8888");
        ws.onopen = function() {
            //ws.send("111");
            console.log(ws.readyState);
        };
        ws.onmessage = function(evt) {
            var received_msg = evt.data;
            console.log(received_msg);
        };

        ws.onclose = function() {
            // 关闭 websocket
        };

        ws.onerror = function(error) {
            alert(error);
            console.log(error);
        };

        function send() {
            ws.send("thank you for accepting this WebSocket request");  
        }
    </script>
</body>

</html>
```
