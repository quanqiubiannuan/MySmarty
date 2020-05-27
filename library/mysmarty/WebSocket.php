<?php
/**
 * Date: 2019/4/20
 * Time: 14:09
 */

namespace library\mysmarty;

abstract class WebSocket
{
    //当前连接
    private $socket;
    private $handshake = false;
    private $client;
    private $pid;
    //绑定ip
    protected $ip = '127.0.0.1';
    //绑定端口
    protected $port = 8888;
//    长度最多为 1024 字节的数据将被接收
    protected $socketRecvLen = 1024;

    public function run()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $this->ip, $this->port);
        socket_listen($this->socket);
        while (true) {
            $client = socket_accept($this->socket);
//            开启子进程处理消息发送
            $pid = pcntl_fork();
//            父进程和子进程都会执行下面代码
            if ($pid == -1) {
//                错误处理：创建子进程失败时返回-1.
//                socket_close($this->socket);
            } else if ($pid) {
//                父进程会得到子进程号，所以这里是父进程执行的逻辑
//                忽略子进程状态
                pcntl_signal(SIGCLD, SIG_IGN);
            } else {
//                子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
                while (true) {
                    $bytes = @socket_recv($client, $buffer, $this->socketRecvLen, 0);
                    if (!$bytes) {
                        posix_kill(posix_getpid(), SIGTERM);
                        break;
                    }

                    if (!$this->handshake) {
                        if ($this->connect($this->getRequestHeader($buffer))) {
                            $this->writeSecWebsocketKey($client, $this->getRequestHeader($buffer, 'sec-websocket-key'));
                            $this->handshake = true;
                            $this->client = $client;
                            $this->pid = posix_getpid();
                        } else {
                            socket_write($client, "HTTP/1.1 400 Bad Request\r\n\r\n服务端拒绝了您的请求");
                        }
                    } else {
                        $this->receive($this->dealMessage($buffer));
                    }
                }
            }
        }
    }

    /**
     * 握手时的函数
     * @param array $header 客户端握手请求头部数据
     * @return bool true，通过握手，false，拒绝握手
     */
    abstract public function connect($header);

    /**
     * 处理接收到的消息
     * @param string $message 客户端发送的消息
     * @return mixed
     */
    abstract public function receive($message);

    /**
     * 关闭客户端连接
     */
    public function closeClient()
    {
        socket_write($this->client, "HTTP/1.1 400 Bad Request\r\n\r\n服务端关闭了您的请求");
        socket_close($this->client);
        posix_kill($this->pid, SIGTERM);
    }

    /**
     * 发送消息
     * @param string $message 客户端发过来的信息
     * @return int
     */
    public function sendMessage($message)
    {
        $messageArr = str_split($message, 125);
        $data = '';
        foreach ($messageArr as $v) {
            $data .= "\x81" . chr(strlen($v)) . $v;
        }
        return socket_write($this->client, $data);
    }

    /**
     * 处理发送过来的数据
     * @param string $buf
     * @return string
     */
    private function dealMessage($buf)
    {
        $decoded = '';
        $len = ord($buf[1]) & 127;
        if ($len === 126) {
            $masks = substr($buf, 4, 4);
            $data = substr($buf, 8);
        } else if ($len === 127) {
            $masks = substr($buf, 10, 4);
            $data = substr($buf, 14);
        } else {
            $masks = substr($buf, 2, 4);
            $data = substr($buf, 6);
        }
        for ($index = 0, $indexMax = strlen($data); $index < $indexMax; $index++) {
            $decoded .= $data[$index] ^ $masks[$index % 4];
        }
        return $decoded;
    }

    /**
     * 创建websocket连接
     * @param resource $client
     * @param string $secWebsocketKey
     */
    private function writeSecWebsocketKey($client, $secWebsocketKey)
    {
        $secWebsocketKey = base64_encode(sha1($secWebsocketKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
            "Upgrade: websocket\r\n" .
            "Connection: Upgrade\r\n" .
            'Sec-WebSocket-Accept: ' . $secWebsocketKey . "\r\n\r\n";
        socket_write($client, $upgrade);
    }

    private function __clone()
    {

    }

    /**
     * 处理请求头协议
     * @param string $header
     * @return array
     */
    private function dealRequestHeader($header)
    {
        $data = [];
        if (!empty($header)) {
            $headerArr = explode("\r\n", $header);
            foreach ($headerArr as $v) {
                if (!preg_match('/:/', $v)) {
                    continue;
                }
                $vArr = explode(':', $v);
                if (count($vArr) >= 2) {
                    $key = strtolower($vArr[0]);
                    unset($vArr[0]);
                    $val = trim(implode(':', $vArr));
                    $data[$key] = $val;
                }
            }
        }
        return $data;
    }

    /**
     * 获取请求头协议相关内容
     * @param string $header
     * @param string $name
     * @param string $defValue
     * @return array|mixed|string
     */
    private function getRequestHeader($header, $name = '', $defValue = '')
    {
        $data = $this->dealRequestHeader($header);
        if (empty($name)) {
            return $data;
        }

        $name = strtolower($name);
        if (isset($data[$name])) {
            return $data[$name];
        }
        return $defValue;
    }
}