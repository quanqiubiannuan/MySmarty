<?php

namespace library\mysmarty;

/**
 * 发送电子邮件类
 */
class Smtp
{
    private string $hostname = CONFIG['mail']['smtp']['hostname'];
    private int $port = CONFIG['mail']['smtp']['port'];
    private int $timeout = CONFIG['mail']['smtp']['timeout'];
    private int $readTimeout = CONFIG['mail']['smtp']['readTimeout'];
    private string $sendEmailUser = CONFIG['mail']['smtp']['sendEmailUser'];
    private string $sendEmailPass = CONFIG['mail']['smtp']['sendEmailPass'];
    private string $showEmail = CONFIG['mail']['smtp']['showEmail'];
    private bool $useSSl = CONFIG['mail']['smtp']['useSSl'];
    private static ?self $obj = null;
    private mixed $handle = false;

    /**
     * 邮件发送.
     * @throws
     */
    private function __construct()
    {
        $this->connect();
    }

    private function __clone()
    {
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (self::$obj === null) {
            self::$obj = new self();
        }
        return self::$obj;
    }

    /**
     * 连接
     */
    private function connect(): void
    {
        $this->handle = fsockopen($this->hostname, $this->port, $errno, $errstr, $this->timeout);
        if ($errno !== 0) {
            exit('连接失败！');
        }
        stream_set_timeout($this->handle, $this->readTimeout);
        stream_set_blocking($this->handle, 1);
        if ($this->useSSl) {
            // 开启安全连接
            stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_SSLv23_CLIENT);
        }
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
        if (self::$obj !== null) {
            self::$obj = null;
        }
    }

    /**
     * 执行
     * @param string $command
     * @param bool $get 是否获取执行结果
     * @return bool|string
     */
    private function exec(string $command, bool $get = true): bool|string
    {
        fwrite($this->handle, $command . "\r\n");
        if ($get) {
            return $this->get();
        }
        return false;
    }

    /**
     * 获取执行的结果
     * @return string
     */
    private function get(): string
    {
        return trim(fgets($this->handle));
    }

    /**
     * 与邮箱通信检测
     * @return bool
     */
    private function helo(): bool
    {
        $result = $this->exec('HELO localhsot');
        $code = $this->getCode($result);
        if ($code === 220) {
            return true;
        }
        return false;
    }

    /**
     * 获取返回字符串的状态码
     * @param string $result
     * @return int
     */
    private function getCode(string $result): int
    {
        return (int)mb_substr($result, 0, 3);
    }

    /**
     * 验证
     * @return bool
     */
    private function auth(): bool
    {
        if (empty($this->sendEmailUser)) {
            return true;
        }
        $user = base64_encode($this->sendEmailUser);
        $pass = base64_encode($this->sendEmailPass);
        $this->exec('AUTH LOGIN ' . $user);
        $this->exec($pass);
        $code = $this->getCode($this->get());
        if ($code === 235) {
            return true;
        }
        return false;
    }

    /**
     * 设置发送者邮箱
     * @return bool
     */
    private function setMailFromEmail(): bool
    {
        $result = $this->exec('MAIL FROM:<' . $this->sendEmailUser . '>');
        $code = $this->getCode($result);
        if ($code !== 250) {
            return false;
        }
        return true;
    }

    /**
     * 设置接收者邮箱
     * @param string $email 接收者邮箱账号
     * @return bool
     */
    private function setRcptTo(string $email): bool
    {
        if (!is_array($email)) {
            $email = [
                [
                    'email' => $email,
                    'type' => 'To'
                ]
            ];
        }
        foreach ($email as $e) {
            $result = $this->exec('RCPT TO:<' . $e['email'] . '>');
            $code = $this->getCode($result);
            if ($code !== 250) {
                return false;
            }
        }
        return true;
    }

    /**
     * 获取文件类型
     * @param string $file
     * @return string
     */
    private function getFileMiMeType(string $file): string
    {
        $mime = mime_content_type($file);
        if (false === strpos($mime, 'image')) {
            $mime = 'application/octet-stream';
        }
        return $mime;
    }

    /**
     * 设置邮件头
     * @param string|array $email 邮箱账号
     * @param string $subject 标题
     * @param string $content 内容
     * @param string $attachment 附件
     * @param bool $isHtml 是否为html邮件
     * @return bool
     */
    private function setEmailHeader(array|string $email, string $subject = '', string $content = '', string $attachment = '', bool $isHtml = true): bool
    {
        $commands = '';
        list ($msec, $sec) = explode(' ', microtime());
        if (!is_array($email)) {
            $email = [
                [
                    'email' => $email,
                    'type' => 'To'
                ]
            ];
        }
        foreach ($email as $e) {
            $commands .= $e['type'] . ': ' . $e['email'] . "\r\n";
        }
        $commands .= 'From: ' . $this->showEmail . '<' . $this->sendEmailUser . '>' . "\r\n";
        $commands .= 'Subject: ' . $subject . "\r\n";
        $commands .= 'Date: ' . date('r') . "\r\n" . 'X-Mailer:By Redhat (PHP/7.1.3)' . "\r\n";
        $commands .= 'Message-ID: <' . date('YmdHis', $sec) . '.' . ($msec * 1000000) . '.' . $this->sendEmailUser . '>' . "\r\n";
        if (!empty($attachment)) {
            // 有附件
            $commands .= 'Content-Type: multipart/mixed;' . "\r\n";
        } else if (preg_match('/<img /i', $content)) {
            // 正文中含有图片
            $commands .= 'Content-Type: multipart/related;' . "\r\n";
        } else {
            // 普通
            $commands .= 'Content-Type: multipart/alternative;' . "\r\n";
        }
        $separator = '----=_Part_' . md5($this->sendEmailUser . time()) . uniqid('', true);
        $commands .= "\t" . 'boundary="' . $separator . '"' . "\r\n";
        $commands .= 'MIME-Version: 1.0' . "\r\n" . "\r\n";
        $commands .= '--' . $separator . "\r\n";
        if ($isHtml) {
            $commands .= 'Content-Type: text/html;charset=utf-8' . "\r\n";
        } else {
            $commands .= 'Content-Type: text/plain;charset=utf-8' . "\r\n";
        }
        $commands .= 'Content-Transfer-Encoding: base64' . "\r\n";
        $commands .= "\r\n" . base64_encode($content) . "\r\n";
        $commands .= '--' . $separator . "\r\n";
        // 附件处理
        if (!empty($attachment)) {
            if (!is_array($attachment)) {
                $attachment = [
                    $attachment
                ];
            }
            foreach ($attachment as $file) {
                if (!file_exists($file)) {
                    exit($file . ' 不存在');
                }
                $commands .= "\r\n" . '--' . $separator . "\r\n";
                $commands .= 'Content-Type: ' . $this->getFileMiMeType($file) . '; name="' . basename($file) . '"' . "\r\n";
                $commands .= 'Content-Transfer-Encoding: base64' . "\r\n";
                $commands .= 'Content-Disposition: attachment; filename="' . basename($file) . '"' . "\r\n" . "\r\n";
                $commands .= base64_encode(file_get_contents($file)) . "\r\n";
                $commands .= '--' . $separator . "\r\n";
            }
        }
        $commands .= "\r\n" . '.';
        $result = $this->exec('DATA');
        if ($this->getCode($result) !== 354) {
            return false;
        }
        $result = $this->exec($commands);
        if ($this->getCode($result) !== 250) {
            return false;
        }
        $result = $this->exec('QUIT');
        if ($this->getCode($result) !== 221) {
            return false;
        }
        return true;
    }

    /**
     * 原始发送邮件方法
     * @param string|array $email
     *            (type 字段，To 普通方式，CC，抄送，BCC，秘密抄送)
     *            接收者邮箱
     *            $email = [
     *            [
     *            'email' => '接收者邮箱1',
     *            'type' => 'CC'
     *            ],
     *            [
     *            'email' => '接收者邮箱2',
     *            'type' => 'BCC'
     *            ],
     *            [
     *            'email' => '接收者邮箱3',
     *            'type' => 'To'
     *            ]
     *            ];
     * @param string $subject 标题
     * @param string $content 内容
     * @param string $attachment
     * @param bool $isHtml 是否为html邮件
     * @return bool
     */
    public function rawSend(array|string $email, string $subject, string $content, string $attachment = '', bool $isHtml = true): bool
    {
        if (!$this->helo()) {
            exit('与邮箱服务器通信失败');
        }
        if (!$this->auth()) {
            exit('邮箱账号密码验证失败');
        }
        if (!$this->setMailFromEmail()) {
            exit('设置发送邮箱账号失败');
        }
        if (!$this->setRcptTo($email)) {
            exit('设置接收邮箱失败');
        }
        if (!$this->setEmailHeader($email, $subject, $content, $attachment, $isHtml)) {
            exit('发送邮件失败');
        }
        return true;
    }

    /**
     * 发送邮件
     * @param string|array $email 接收者邮箱，字符串或数组格式的邮箱
     * @param string $subject 标题
     * @param string $content 内容
     * @param string $attachment 邮件附件，字符串或数组
     * @param bool $isHtml 是否为html格式邮件
     * @param string $type 发送类型， To 普通方式，CC，抄送，BCC，秘密抄送
     * @return bool
     */
    public function send(array|string $email, string $subject, string $content, string $attachment = '', bool $isHtml = true, $type = 'To'): bool
    {
        $tmp = [];
        if (!is_array($email)) {
            $tmp[] = [
                'email' => $email,
                'type' => $type
            ];
        } else {
            foreach ($email as $e) {
                $tmp[] = [
                    'email' => $e,
                    'type' => $type
                ];
            }
        }
        return $this->rawSend($tmp, $subject, $content, $attachment, $isHtml);
    }
}