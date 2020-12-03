<?php

namespace library\mysmarty;

class Redis
{
    // redis ip
    private static string $host = CONFIG['database']['redis']['host'];
    // redis 端口
    private static int $port = CONFIG['database']['redis']['port'];
    // redis 密码
    private static string $pass = CONFIG['database']['redis']['pass'];
    // redis 连接超时时间，单位，秒
    private static int $connectTimeOut = 5;
    // redis 读取超时时间，单位，秒
    private static int $readTimeOut = 3;
    private static $handle = null;
    // 是否开启了事务
    private static bool $isMulti = false;
    private static ?self $obj = null;

    private function __construct()
    {
        self::$handle = @fsockopen(self::$host, self::$port, $errno, $errstr, self::$connectTimeOut);
        if ($errno !== 0) {
            exit();
        }
        if (!isCliMode()) {
            stream_set_timeout(self::$handle, self::$readTimeOut);
        } else {
            stream_set_timeout(self::$handle, PHP_INT_MAX);
        }
        if (!empty(self::$pass)) {
            $this->auth(self::$pass);
        }
    }

    private function __clone()
    {
    }

    /**
     * 使用自定义配置的redis连接
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
     * 执行命令
     * @param string $command
     * @return array|bool|int|string
     */
    public function exec(string $command = ''): array|bool|int|string
    {
        if (empty($command)) {
            if (self::$isMulti) {
                $command = 'EXEC';
            } else {
                return false;
            }
        }
        fwrite(self::$handle, $command . "\r\n");
        return $this->getResult();
    }

    /**
     * @return bool|int|array|string
     */
    private function getResult(): bool|int|array|string
    {
        $char = fgetc(self::$handle);
        $result = trim(fgets(self::$handle));
        switch ($char) {
            case '+':
                // 返回一行结果
                break;
            case ':':
                $result = (int)$result;
                break;
            case '$':
                if ($result !== '-1') {
                    $len = (int)$result;
                    $result = '';
                    for ($i = 0; $i < $len;) {
                        $diff = $len - $i;
                        $block_size = $diff > 8192 ? 8192 : $diff;
                        $chunk = fread(self::$handle, $block_size);
                        if ($chunk !== false) {
                            $chunkLen = strlen($chunk);
                            $i += $chunkLen;
                            $result .= $chunk;
                        } else {
                            fseek(self::$handle, $i);
                        }
                    }
                    fgets(self::$handle);
                } else {
                    $result = false;
                }
                break;
            case '*':
                if ($result !== '-1') {
                    $len = (int)$result;
                    $result = [];
                    for ($i = 0; $i < $len; $i++) {
                        $result[] = $this->getResult();
                    }
                } else {
                    $result = false;
                }
                break;

            case '-':
                $result = false;
                break;
        }
        return $result;
    }

    /**
     * 设置一个key - value
     * @param string $key 键
     * @param string $value 值
     * @param int $expire 过期时间，单位，秒
     * @return bool 成功返回 true,失败返回false
     */
    public function set(string $key, string $value, int $expire = -1): bool
    {
        $result = $this->cmd('set', $key, $value);
        if (!$this->isOk($result)) {
            return false;
        }
        if ($expire > 0) {
            $result = $this->cmd('EXPIRE', $key, $expire);
            if ($result !== 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * 关闭连接
     */
    public function __destruct()
    {
        if (is_resource(self::$handle)) {
            fclose(self::$handle);
        }
    }

    /**
     * 删除 key
     * @param string $key 要删除的key值
     * @return int|bool|array|string 返回删除的key数量
     */
    public function del(string $key): int|bool|array|string
    {
        return $this->cmd('del', $key);
    }

    /**
     * 序列化键值
     * @param string $key
     * @return int|bool|array|string
     */
    public function dump(string $key): int|bool|array|string
    {
        return $this->cmd('DUMP', $key);
    }

    /**
     * 判断一个键值是否存在
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $result = $this->cmd('EXISTS', $key);
        return $result === 1;
    }

    /**
     * 设置过期时间
     * @param string $key 键
     * @param int $expire 过期时长，单位，秒
     * @return bool
     */
    public function expire(string $key, int $expire): bool
    {
        $result = $this->cmd('Expire', $key, $expire);
        return $result === 1;
    }

    /**
     * 用时间戳设置过期时间
     * @param string $key 键
     * @param int $time 过期时间的时间戳
     * @return bool
     */
    public function expireat(string $key, int $time): bool
    {
        $result = $this->cmd('Expireat', $key, $time);
        return $result === 1;
    }

    /**
     * 以时间戳设置过期时间
     * @param string $key 键
     * @param int $time 过期时间戳，单位，毫秒
     * @return bool
     */
    public function pexpireat(string $key, int $time): bool
    {
        $result = $this->cmd('PEXPIREAT', $key, $time);
        return $result === 1;
    }

    /**
     * 用毫秒设置过期时间
     * @param string $key 键
     * @param int $expire 过期时间，单位，毫秒
     * @return bool
     */
    public function pexpire(string $key, int $expire): bool
    {
        $result = $this->cmd('PEXPIRE', $key, $expire);
        return $result === 1;
    }

    /**
     * 获取键的剩余时间，单位秒
     * @param string $key
     * @return int|bool|array|string
     */
    public function ttl(string $key): int|bool|array|string
    {
        return $this->cmd('ttl', $key);
    }

    /**
     * 获取键的剩余时间，单位毫秒
     * @param string $key
     * @return int|bool|array|string
     */
    public function pttl(string $key): int|bool|array|string
    {
        return $this->cmd('pttl', $key);
    }

    /**
     * 匹配相应的模式key数据
     * @param string $patter 匹配模式
     * @return int|bool|array|string
     */
    public function keys(string $patter): int|bool|array|string
    {
        return $this->cmd('KEYS', $patter);
    }

    /**
     * 移动key到其它redis库
     * @param string $key 键
     * @param int $db redis库, 0-15
     * @return bool
     */
    public function move(string $key, int $db): bool
    {
        $result = $this->cmd('MOVE', $key, $db);
        return $result === 1;
    }

    /**
     * 切换redis库
     * @param int $db redis库, 0-15
     * @return bool
     */
    public function select(int $db): bool
    {
        $result = $this->cmd('SELECT', $db);
        return $this->isOk($result);
    }

    /**
     * 选择redis库
     * @param int $db 0 - 15
     * @return $this
     */
    public function setDb(int $db): static
    {
        $this->select($db);
        return $this;
    }

    /**
     * 移除过期时间
     * @param string $key 键
     * @return bool
     */
    public function persist(string $key): bool
    {
        $result = $this->cmd('PERSIST', $key);
        return $result === 1;
    }

    /**
     * 随机返回一个键
     * @return int|bool|array|string
     */
    public function randomKey(): int|bool|array|string
    {
        return $this->cmd('RANDOMKEY');
    }

    /**
     * 清除当前选择的redis库所有数据
     * @return bool
     */
    public function flushDb(): bool
    {
        $result = $this->cmd('FLUSHDB');
        return $this->isOk($result);
    }

    /**
     * 判断返回的是不是ok字符串
     * @param string $value
     * @return bool
     */
    private function isOk(string $value): bool
    {
        return is_string($value) && strtoupper($value) === 'OK';
    }

    /**
     * 重命名key
     * @param string $key 旧键值
     * @param string $newKey 新键值
     * @return bool
     */
    public function rename(string $key, string $newKey): bool
    {
        $result = $this->cmd('RENAME', $key, $newKey);
        return $this->isOk($result);
    }

    /**
     * 重命名key,在新的 key 不存在时修改 key 的名称
     * @param string $key 旧键值
     * @param string $newKey 新键值
     * @return bool
     */
    public function renamenx(string $key, string $newKey): bool
    {
        $result = $this->cmd('RENAMENX', $key, $newKey);
        return $result === 1;
    }

    /**
     * 返回key的类型
     * @param string $key
     * @return int|bool|array|string
     *         none (key不存在)
     *         string (字符串)
     *         list (列表)
     *         set (集合)
     *         zset (有序集)
     *         hash (哈希表)
     */
    public function type(string $key): int|bool|array|string
    {
        return $this->cmd('TYPE', $key);
    }

    /**
     * 获取key对应的值
     * @param string $key
     * @return int|bool|array|string
     */
    public function get(string $key): int|bool|array|string
    {
        return $this->cmd('GET', $key);
    }

    /**
     * 获取存储在指定 key 中字符串的子字符串
     * @param string $key 键
     * @param int $start 开始位置，0开始
     * @param int $end 结束位置
     * @return int|bool|array|string
     */
    public function getRange(string $key, int $start, int $end): int|bool|array|string
    {
        return $this->cmd('GETRANGE', $key, $start, $end);
    }

    /**
     * 设置指定 key的值，并返回 key 的旧值。
     * @param string $key 键
     * @param string $value 新值
     * @return int|bool|array|string
     */
    public function getSet(string $key, string $value): int|bool|array|string
    {
        return $this->cmd('GETSET', $key, $value);
    }

    /**
     * 对 key所储存的字符串值，获取指定偏移量上的位(bit)
     * @param string $key 键
     * @param int $offset 偏移量上的位(bit)
     * @return int|bool|array|string
     */
    public function getBit(string $key, int $offset): int|bool|array|string
    {
        return $this->cmd('GETBIT', $key, $offset);
    }

    /**
     * 对 key所储存的字符串值，设置或清除指定偏移量上的位(bit)
     * @param string $key 键
     * @param int $offset 偏移量上的位(bit)
     * @param int $value 只能是0或1
     * @return array|bool|int|string
     */
    public function setBit(string $key, int $offset, int $value): array|bool|int|string
    {
        if ($value !== 0 && $value !== 1) {
            return false;
        }
        return $this->cmd('SETBIT', $key, $offset, $value);
    }

    /**
     * 返回所有(一个或多个)给定 key 的值
     * @param array $keys 数组
     * @return int|bool|array|string
     */
    public function mGet(array $keys): int|bool|array|string
    {
        $keys = array_merge([
            'MGET'
        ], $keys);
        return $this->cmd2($keys);
    }

    /**
     * 为指定的 key 设置值及其过期时间
     * @param string $key 键
     * @param int $timeOut 过期时间，单位秒
     * @param string $value 新值
     * @return bool
     */
    public function setEx(string $key, int $timeOut, string $value): bool
    {
        $result = $this->cmd('SETEX', $key, $timeOut, $value);
        return $this->isOk($result);
    }

    /**
     * 在指定的 key 不存在时，为 key 设置指定的值
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setNx(string $key, string $value): bool
    {
        $result = $this->cmd('SETNX', $key, $value);
        return $result === 1;
    }

    /**
     * 用指定的字符串覆盖给定 key 所储存的字符串值，覆盖的位置从偏移量 offset 开始
     * @param string $key 键
     * @param int $offset 偏移量
     * @param string $value 值
     * @return int|bool|array|string
     */
    public function setRanger(string $key, int $offset, string $value): int|bool|array|string
    {
        return $this->cmd('SETRANGE', $key, $offset, $value);
    }

    /**
     * 获取指定 key 所储存的字符串值的长度
     * @param string $key
     * @return int|bool|array|string
     */
    public function strlen(string $key): int|bool|array|string
    {
        return $this->cmd('STRLEN', $key);
    }

    /**
     * 同时设置一个或多个 key-value 对
     * @param array $data 键值对数组
     * @return bool
     */
    public function mSet(array $data): bool
    {
        $tmp = [];
        foreach ($data as $key => $val) {
            $tmp[] = $key;
            $tmp[] = $val;
        }
        $result = $this->cmd2(array_merge([
            'MSET'
        ], $tmp));
        return $this->isOk($result);
    }

    /**
     * 用于所有给定 key 都不存在时，同时设置一个或多个 key-value 对
     * @param array $data 键值对数组
     * @return bool
     */
    public function mSetNx(array $data): bool
    {
        $tmp = [];
        foreach ($data as $key => $val) {
            $tmp[] = $key;
            $tmp[] = $val;
        }
        $result = $this->cmd2(array_merge([
            'MSETNX'
        ], $tmp));
        return $result === 1;
    }

    /**
     * 以毫秒为单位设置 key 的生存时间
     * @param string $key 键
     * @param int $timeOut 过期时间，单位毫秒
     * @param string $value 值
     * @return bool
     */
    public function pSetEx(string $key, int $timeOut, string $value): bool
    {
        $result = $this->cmd('PSETEX', $key, $timeOut, $value);
        return $this->isOk($result);
    }

    /**
     * cmd操作
     * @param mixed ...$args
     * @return bool|string|array|integer
     */
    public function cmd(...$args): array|bool|int|string
    {
        return $this->execArgs($args);
    }

    /**
     * 传递数组格式的数据执行
     * @param array $args
     * @return array|bool|int|string
     */
    public function cmd2(array $args): array|bool|int|string
    {
        return $this->execArgs($args);
    }

    /**
     * 执行命令
     * @param array $args
     * @return int|bool|array|string
     */
    private function execArgs(array $args): int|bool|array|string
    {
        $len = count($args);
        $command = '*' . $len . "\r\n";
        foreach ($args as $arg) {
            $command .= '$' . strlen($arg) . "\r\n" . $arg . "\r\n";
        }
        return $this->exec($command);
    }

    /**
     * 将 key 中储存的数字值增一
     * @param string $key
     * @return array|bool|int|string
     */
    public function incr(string $key): array|bool|int|string
    {
        return $this->cmd('INCR', $key);
    }

    /**
     * 将 key 中储存的数字加上指定的增量值
     * @param string $key 键
     * @param int $amount 指定增量
     * @return array|bool|int|string
     */
    public function incrBy(string $key, int $amount): array|bool|int|string
    {
        return $this->cmd('INCRBY', $key, $amount);
    }

    /**
     * 将 key 中储存的数字值减一
     * @param string $key
     * @return array|bool|int|string
     */
    public function decr(string $key): array|bool|int|string
    {
        return $this->cmd('DECR', $key);
    }

    /**
     * 将 key 所储存的值减去指定的减量值
     * @param string $key 键
     * @param int $amount 指定减量
     * @return array|bool|int|string
     */
    public function decrBy(string $key, int $amount): array|bool|int|string
    {
        return $this->cmd('DECRBY', $key, $amount);
    }

    /**
     * 用于为指定的 key 追加值
     * @param string $key
     * @param string $newValue
     * @return array|bool|int|string
     */
    public function append(string $key, string $newValue): array|bool|int|string
    {
        return $this->cmd('APPEND', $key, $newValue);
    }

    /**
     * 同时将多个 field-value (字段-值)对设置到哈希表中
     * @param string $key
     * @param array $data 键值对数组
     * @return bool
     */
    public function hmSet(string $key, array $data): bool
    {
        $tmp = [];
        foreach ($data as $k => $v) {
            $tmp[] = $k;
            $tmp[] = $v;
        }
        $result = $this->cmd2(array_merge([
            'HMSET',
            $key
        ], $tmp));
        return $this->isOk($result);
    }

    /**
     * 删除哈希表 key 中的一个或多个指定字段
     * @param string $key
     * @param mixed $fields 可变参数
     * @return int|bool|array|string
     */
    public function hDel(string $key, ...$fields): int|bool|array|string
    {
        return $this->cmd2(array_merge([
            'HDEL',
            $key
        ], $fields));
    }

    /**
     * 查看哈希表的指定字段是否存在
     * @param string $key
     * @param string $field
     * @return bool
     */
    public function hExists(string $key, string $field): bool
    {
        $result = $this->cmd('HEXISTS', $key, $field);
        return $result === 1;
    }

    /**
     * 返回哈希表中指定字段的值
     * @param string $key
     * @param string $field
     * @return array|bool|int|string
     */
    public function hGet(string $key, string $field): array|bool|int|string
    {
        return $this->cmd('HGET', $key, $field);
    }

    /**
     * 返回哈希表中，所有的字段和值
     * @param string $key
     * @return array
     */
    public function hGetAll(string $key): array
    {
        $result = $this->cmd('HGETALL', $key);
        return $this->toAssocArr($result);
    }

    /**
     * 将数组转为键值对数组
     * @param array $result
     * @return array
     */
    public function toAssocArr(array $result): array
    {
        $data = [];
        if (is_array($result)) {
            $tmp = '';
            foreach ($result as $k => $v) {
                if ($k % 2 !== 0) {
                    $data[$tmp] = $v;
                } else {
                    $tmp = $v;
                }
            }
        }
        return $data;
    }

    /**
     * 为哈希表中的字段值加上指定增量值
     * @param string $key
     * @param string $field
     * @param int $amount
     * @return array|bool|int|string
     */
    public function hIncrBy(string $key, string $field, int $amount): array|bool|int|string
    {
        return $this->cmd('HINCRBY', $key, $field, $amount);
    }

    /**
     * 为哈希表中的字段值加上指定浮点数增量值
     * @param string $key
     * @param string $field
     * @param float $amount
     * @return array|bool|int|string
     */
    public function hIncrByFloat(string $key, string $field, float $amount): array|bool|int|string
    {
        return $this->cmd('HINCRBYFLOAT', $key, $field, $amount);
    }

    /**
     * 获取哈希表中的所有域（field）
     * @param string $key
     * @return array|bool|int|string
     */
    public function hKeys(string $key): array|bool|int|string
    {
        return $this->cmd('HKEYS', $key);
    }

    /**
     * 获取哈希表中字段的数量
     * @param string $key
     * @return array|bool|int|string
     */
    public function hLen(string $key): array|bool|int|string
    {
        return $this->cmd('HLEN', $key);
    }

    /**
     * 返回哈希表中，一个或多个给定字段的值
     * @param string $key
     * @param string|array $fields 空格分隔的字符串或数组
     * @return array|bool|int|string
     */
    public function hmGet(string $key, array|string $fields): array|bool|int|string
    {
        if (is_array($fields)) {
            $fields = implode(' ', $fields);
        }
        return $this->cmd('HMGET', $key, $fields);
    }

    /**
     * 用于为哈希表中的字段赋值
     * @param string $key
     * @param string $field
     * @param string $value
     * @return bool
     */
    public function hSet(string $key, string $field, string $value): bool
    {
        $this->cmd('HSET', $key, $field, $value);
        return true;
    }

    /**
     * 为哈希表中不存在的的字段赋值
     * @param string $field
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function hSetNx(string $field, string $key, string $value): bool
    {
        $result = $this->cmd('HSETNX', $key, $field, $value);
        return $result === 1;
    }

    /**
     * 返回哈希表所有域(field)的值
     * @param string $key
     * @return array|bool|int|string
     */
    public function hVals(string $key): array|bool|int|string
    {
        return $this->cmd('HVALS', $key);
    }

    /**
     * 迭代哈希表中的键值对
     * @param string $key
     * @param int $cursor
     * @param string $pattern
     * @param string $count
     * @return array|bool|int|string
     */
    public function hScan(string $key, int $cursor, string $pattern = '', string $count = ''): array|bool|int|string
    {
        if (!empty($pattern)) {
            $pattern = 'match ' . $pattern;
        }

        if (!empty($count)) {
            $count = 'count ' . $count;
        }
        $result = $this->cmd('HSCAN', $key, $cursor, $pattern, $count);
        if (isset($result[1])) {
            $result[1] = $this->toAssocArr($result[1]);
        }
        return $result;
    }

    /**
     * 将一个或多个值插入到列表头部
     * @param string $key
     * @param string $value 空格分隔或数组
     * @return array|bool|int|string
     */
    public function lPush(string $key, string $value): array|bool|int|string
    {
        if (is_array($value)) {
            $value = implode(' ', $value);
        }
        return $this->cmd('LPUSH', $key, $value);
    }

    /**
     * 移出并获取列表的第一个元素
     * @param string $key 元素
     * @param int $timeOut
     * @return int|bool|array|string
     */
    public function blPop(string $key, int $timeOut = 1): int|bool|array|string
    {
        $result = $this->cmd('BLPOP', $key, $timeOut);
        if ($result) {
            $result = $this->toAssocArr($result);
        }
        return $result;
    }

    /**
     * 移出并获取列表的最后一个元素
     * @param string $key 元素
     * @param int $timeOut 超时时间
     * @return int|bool|array|string
     */
    public function brPop(string $key, int $timeOut = 1): int|bool|array|string
    {
        $result = $this->cmd('BRPOP', $key, $timeOut);
        if ($result) {
            $result = $this->toAssocArr($result);
        }
        return $result;
    }

    /**
     * 将弹出的元素插入到另外一个列表中并返回它
     * @param string $key 元素
     * @param string $newKey 新元素
     * @param int $timeOut
     * @return array|bool|int|string
     */
    public function brPopLPush(string $key, string $newKey, int $timeOut = 1): array|bool|int|string
    {
        return $this->cmd('BRPOPLPUSH', $key, $newKey, $timeOut);
    }

    /**
     * 通过索引获取列表中的元素
     * @param string $key
     * @param int $position 位置，从0开始
     * @return array|bool|int|string
     */
    public function lIndex(string $key, int $position): array|bool|int|string
    {
        return $this->cmd('LINDEX', $key, $position);
    }

    /**
     * 在列表的元素前或者后插入元素
     * @param string $key
     * @param string $values
     * @param string $pivot 元素中的值
     * @param bool $isBefore 是否插入在前面
     * @return array|bool|int|string
     */
    public function lInsert(string $key, string $values, string $pivot, bool $isBefore = true): array|bool|int|string
    {
        $bf = 'BEFORE';
        if (!$isBefore) {
            $bf = 'AFTER';
        }
        return $this->cmd('LINSERT', $key, $bf, $pivot, $values);
    }

    /**
     * 返回列表的长度
     * @param string $key
     * @return array|bool|int|string
     */
    public function lLen(string $key): array|bool|int|string
    {
        return $this->cmd('LLEN', $key);
    }

    /**
     * 移除并返回列表的第一个元素
     * @param string $key
     * @return array|bool|int|string
     */
    public function lPop(string $key): array|bool|int|string
    {
        return $this->cmd('Lpop', $key);
    }

    /**
     * 将一个值插入到已存在的列表头部
     * @param string $key
     * @param string $value
     * @return array|bool|int|string
     */
    public function lPushX(string $key, string $value): array|bool|int|string
    {
        return $this->cmd('LPUSHX', $key, $value);
    }

    /**
     * 返回列表中指定区间内的元素
     * @param string $key
     * @param int $start 开始位置，从0开始
     * @param int $end 结束位置，从0开始
     * @return array|bool|int|string
     */
    public function lRange(string $key, int $start, int $end): array|bool|int|string
    {
        return $this->cmd('LRANGE', $key, $start, $end);
    }

    /**
     * 根据参数 COUNT 的值，移除列表中与参数 VALUE 相等的元素
     * @param string $key
     * @param string $value 要移除的值
     * @param int $count
     *            count > 0 : 从表头开始向表尾搜索，移除与 VALUE 相等的元素，数量为 COUNT 。
     *            count < 0 : 从表尾开始向表头搜索，移除与 VALUE 相等的元素，数量为 COUNT 的绝对值。
     *            count = 0 : 移除表中所有与 VALUE 相等的值。
     * @return array|bool|int|string
     */
    public function lRem(string $key, string $value, int $count = 0): array|bool|int|string
    {
        return $this->cmd('LREM', $key, $count, $value);
    }

    /**
     * 通过索引来设置元素的值
     * @param string $key
     * @param int $position 位置/索引
     * @param string $value
     * @return bool
     */
    public function lSet(string $key, int $position, string $value): bool
    {
        $result = $this->cmd('LSET', $key, $position, $value);
        return $this->isOk($result);
    }

    /**
     * 让列表只保留指定区间内的元素
     * @param string $key
     * @param int $start
     * @param int $end
     * @return bool
     */
    public function lTrim(string $key, int $start, int $end): bool
    {
        $result = $this->cmd('LTRIM', $key, $start, $end);
        return $this->isOk($result);
    }

    /**
     * 移除列表的最后一个元素
     * @param string $key
     * @return array|bool|int|string
     */
    public function rPop(string $key): array|bool|int|string
    {
        return $this->cmd('RPOP', $key);
    }

    /**
     * 移除列表的最后一个元素，并将该元素添加到另一个列表并返回
     * @param string $key
     * @param string $newKey
     * @return array|bool|int|string
     */
    public function rPopLPush(string $key, string $newKey): array|bool|int|string
    {
        return $this->cmd('RPOPLPUSH', $key, $newKey);
    }

    /**
     * 将一个或多个值插入到列表的尾部
     * @param string $key
     * @param string|array $values 空格分隔或数组
     * @return array|bool|int|string
     */
    public function rPush(string $key, array|string $values): array|bool|int|string
    {
        if (is_array($values)) {
            $values = implode(' ', $values);
        }
        return $this->cmd('RPUSH', $key, $values);
    }

    /**
     * 将一个值插入到已存在的列表尾部(最右边)。如果列表不存在，操作无效
     * @param string $key
     * @param string $value
     * @return array|bool|int|string
     */
    public function rPushX(string $key, string $value): array|bool|int|string
    {
        return $this->cmd('RPUSHX', $key, $value);
    }

    /**
     * 将一个或多个成员元素加入到集合中，已经存在于集合的成员元素将被忽略
     * @param string $key
     * @param string|array $values 空格分隔或数组
     * @return array|bool|int|string
     */
    public function sAdd(string $key, array|string $values): array|bool|int|string
    {
        if (is_array($values)) {
            $values = implode(' ', $values);
        }
        return $this->cmd('SADD', $key, $values);
    }

    /**
     * 返回集合中元素的数量
     * @param string $key
     * @return array|bool|int|string
     */
    public function sCard(string $key): array|bool|int|string
    {
        return $this->cmd('SCARD', $key);
    }

    /**
     * 回给定集合之间的差集。不存在的集合 key 将视为空集
     * @param string $key
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function sDiff(string $key, ...$keys): array|bool|int|string
    {
        return $this->cmd('SDIFF', $key, implode(' ', $keys));
    }

    /**
     * 将给定集合之间的差集存储在指定的集合中
     * @param string $key
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function sDiffStore(string $key, ...$keys): array|bool|int|string
    {
        return $this->cmd('SDIFFSTORE', $key, implode(' ', $keys));
    }

    /**
     * 返回给定所有给定集合的交集
     * @param string $key
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function sInter(string $key, ...$keys): array|bool|int|string
    {
        return $this->cmd('SINTER', $key, implode(' ', $keys));
    }

    /**
     * 将给定集合之间的交集存储在指定的集合中
     * @param string $key
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function sInterStore(string $key, ...$keys): array|bool|int|string
    {
        return $this->cmd('SINTERSTORE', $key, implode(' ', $keys));
    }

    /**
     * 判断成员元素是否是集合的成员
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function sIsMember(string $key, string $value): bool
    {
        $result = $this->cmd('SISMEMBER', $key, $value);
        return $result === 1;
    }

    /**
     * 返回集合中的所有的成员
     * @param string $key
     * @return array|bool|int|string
     */
    public function sMembers(string $key): array|bool|int|string
    {
        return $this->cmd('SMEMBERS', $key);
    }

    /**
     * 将指定成员 member 元素从 source 集合移动到 destination 集合
     * @param string $member
     * @param string $source
     * @param string $destination
     * @return bool|string
     */
    public function sMove(string $member, string $source, string $destination): bool|string
    {
        $result = $this->cmd('SMOVE', $source, $destination, $member);
        return $result === 1;
    }

    /**
     * 移除集合中的指定 key 的一个或多个随机元素
     * @param string $key
     * @param int $count 要移除几个元素
     * @return array|bool|int|string
     */
    public function sPop(string $key, int $count = 1): array|bool|int|string
    {
        return $this->cmd('SPOP', $key, $count);
    }

    /**
     * 返回集合中的一个随机元素
     * @param string $key
     * @param int $num
     * @return array|bool|int|string
     */
    public function sRandMember(string $key, int $num = 1): array|bool|int|string
    {
        return $this->cmd('SRANDMEMBER', $key, $num);
    }

    /**
     * 移除集合中的一个或多个成员元素
     * @param string $key
     * @param array $members
     * @return array|bool|int|string
     */
    public function sRem(string $key, array ...$members): array|bool|int|string
    {
        return $this->cmd('SREM', $key, implode(' ', $members));
    }

    /**
     * 返回给定集合的并集
     * @param string $key
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function sUnion(string $key, ...$keys): array|bool|int|string
    {
        return $this->cmd('SUNION', $key, implode(' ', $keys));
    }

    /**
     * 将给定集合的并集存储在指定的集合 key 中
     * @param string $key
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function sUnionStore(string $key, ...$keys): array|bool|int|string
    {
        return $this->cmd('SUNIONSTORE', $key, implode(' ', $keys));
    }

    /**
     * 用于迭代集合中键的元素
     * @param string $key
     * @param int $cursor
     * @param string $pattern
     * @param string $count
     * @return array|bool|int|string
     */
    public function sScan(string $key, int $cursor, string $pattern = '', string $count = ''): array|bool|int|string
    {
        if (!empty($pattern)) {
            $pattern = 'match ' . $pattern;
        }

        if (!empty($count)) {
            $count = 'count ' . $count;
        }
        $result = $this->cmd('SSCAN', $key, $cursor, $pattern, $count);
        if (isset($result[1])) {
            $result[1] = $this->toAssocArr($result[1]);
        }
        return $result;
    }

    /**
     * 用于将一个或多个成员元素及其分数值加入到有序集当中
     * @param string $key
     * @param string $value
     * @param int $score 排序分值
     * @return array|bool|int|string
     */
    public function zAdd(string $key, string $value, int $score = 1): array|bool|int|string
    {
        return $this->cmd('ZADD', $key, $score, $value);
    }

    /**
     * 用于将一个或多个成员元素及其分数值加入到有序集当中
     * @param string $key
     * @param array $data 二维数组，例如：[['score' => 1,'value' => 'hello']]
     * @return array|bool|int|string
     */
    public function zAdds(string $key, array $data): array|bool|int|string
    {
        $str = '';
        foreach ($data as $val) {
            if (empty($str)) {
                $str = $val['score'] . ' ' . $val['value'];
            } else {
                $str .= ' ' . $val['score'] . ' ' . $val['value'];
            }
        }
        return $this->cmd('ZADD', $key, $str);
    }

    /**
     * 计算集合中元素的数量
     * @param string $key
     * @return array|bool|int|string
     */
    public function zCard(string $key): array|bool|int|string
    {
        return $this->cmd('ZCARD', $key);
    }

    /**
     * 计算有序集合中指定分数区间的成员数量
     * @param string $key
     * @param int $minScore 最低分
     * @param int $maxScore 最高分
     * @return array|bool|int|string
     */
    public function zCount(string $key, int $minScore, int $maxScore): array|bool|int|string
    {
        return $this->cmd('ZCOUNT', $key, $minScore, $maxScore);
    }

    /**
     * 对有序集合中指定成员的分数加上增量 increment
     * @param string $key
     * @param string $member
     * @param int|float $increment 分数值可以是整数值或双精度浮点数
     * @return array|bool|int|string
     */
    public function zIncrBy(string $key, string $member, int|float $increment): array|bool|int|string
    {
        return $this->cmd('ZINCRBY', $key, $increment, $member);
    }

    /**
     * 计算给定的一个或多个有序集的交集，其中给定 key 的数量必须以 numkeys 参数指定，并将该交集(结果集)储存到 destination
     * @param string $destination
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function zInterStore(string $destination, ...$keys): array|bool|int|string
    {
        return $this->cmd('ZINTERSTORE', $destination, count($keys), implode(' ', $keys));
    }

    /**
     * 计算有序集合中指定字典区间内成员数量
     * @param string $key
     * @param string $min
     * @param string $max
     * @return array|bool|int|string
     */
    public function zLexCount(string $key, string $min, string $max): array|bool|int|string
    {
        return $this->cmd('ZLEXCOUNT', $key, $min, $max);
    }

    /**
     * 返回有序集中，指定区间内的成员
     * @param string $key
     * @param int $start 开始下标
     * @param int $stop 结束下标
     * @param bool $withScores 是否显示分数值
     * @return int|bool|array|string
     */
    public function zRange(string $key, int $start, int $stop, bool $withScores = false): int|bool|array|string
    {
        $result = $this->cmd('ZRANGE', $key, $start, $stop, $withScores ? 'WITHSCORES' : '');
        if ($withScores && $result) {
            $data = [];
            $tmp = [];
            foreach ($result as $k => $v) {
                if ($k % 2 === 0) {
                    $tmp['value'] = $v;
                } else {
                    $tmp['score'] = $v;
                    $data[] = $tmp;
                }
            }
            $result = $data;
        }
        return $result;
    }

    /**
     * 通过字典区间返回有序集合的成员
     * @param string $key
     * @param string $min
     * @param string $max
     * @return array|bool|int|string
     */
    public function zRangeByLex(string $key, string $min, string $max): array|bool|int|string
    {
        return $this->cmd('ZRANGEBYLEX', $key, $min, $max);
    }

    /**
     * 返回有序集合中指定分数区间的成员列表
     * @param string $key
     * @param string $min 最低分
     * @param string $max 最高分
     * @param bool $withScores
     * @return int|bool|array|string
     */
    public function zRangeByScore(string $key, string $min, string $max, bool $withScores = false): int|bool|array|string
    {
        $result = $this->cmd('ZRANGEBYSCORE', $key, $min, $max, $withScores ? 'WITHSCORES' : '');
        if ($withScores && $result) {
            $data = [];
            $tmp = [];
            foreach ($result as $k => $v) {
                if ($k % 2 === 0) {
                    $tmp['value'] = $v;
                } else {
                    $tmp['score'] = $v;
                    $data[] = $tmp;
                }
            }
            $result = $data;
        }
        return $result;
    }

    /**
     * 返回有序集中指定分数区间内的所有的成员。有序集成员按分数值递减(从大到小)的次序排列
     * @param string $key
     * @param int $min
     * @param int $max
     * @param bool $withScores
     * @return int|bool|array|string
     */
    public function zRevRangeByScore(string $key, int $min, int $max, bool $withScores = false): int|bool|array|string
    {
        $result = $this->cmd('ZREVRANGEBYSCORE', $key, $max, $min, $withScores ? 'WITHSCORES' : '');
        if ($withScores && $result) {
            $data = [];
            $tmp = [];
            foreach ($result as $k => $v) {
                if ($k % 2 === 0) {
                    $tmp['value'] = $v;
                } else {
                    $tmp['score'] = $v;
                    $data[] = $tmp;
                }
            }
            $result = $data;
        }
        return $result;
    }

    /**
     * 返回有序集中指定成员的排名
     * @param string $key
     * @param string $member
     * @return array|bool|int|string
     */
    public function zRank(string $key, string $member): array|bool|int|string
    {
        return $this->cmd('ZRANK', $key, $member);
    }

    /**
     * 返回有序集中指定成员的排名,其中有序集成员按分数值递减(从大到小)排序
     * @param string $key
     * @param string $member
     * @return array|bool|int|string
     */
    public function zRevRank(string $key, string $member): array|bool|int|string
    {
        return $this->cmd('ZREVRANK', $key, $member);
    }

    /**
     * 移除有序集中的一个或多个成员
     * @param string $key
     * @param mixed $members
     * @return array|bool|int|string
     */
    public function zRem(string $key, ...$members): array|bool|int|string
    {
        return $this->cmd('ZREM', $key, implode(' ', $members));
    }

    /**
     * 移除有序集合中给定的字典区间的所有成员
     * @param string $key
     * @param string $min
     * @param string $max
     * @return array|bool|int|string
     */
    public function zRemRangeByLex(string $key, string $min, string $max): array|bool|int|string
    {
        return $this->cmd('ZREMRANGEBYLEX', $key, $min, $max);
    }

    /**
     * 移除有序集中，指定排名(rank)区间内的所有成员
     * @param string $key
     * @param int $start
     * @param int $stop
     * @return array|bool|int|string
     */
    public function zRemRangeByRank(string $key, int $start, int $stop): array|bool|int|string
    {
        return $this->cmd('ZREMRANGEBYRANK', $key, $start, $stop);
    }

    /**
     * 用于移除有序集中，指定分数（score）区间内的所有成员
     * @param string $key
     * @param int $min
     * @param int $max
     * @return array|bool|int|string
     */
    public function zRemRangeByScore(string $key, int $min, int $max): array|bool|int|string
    {
        return $this->cmd('ZREMRANGEBYSCORE', $key, $min, $max);
    }

    /**
     * 返回有序集中，指定区间内的成员,其中成员的位置按分数值递减(从大到小)来排列
     * @param string $key
     * @param int $start
     * @param int $stop
     * @param bool $withScores
     * @return int|bool|array|string
     */
    public function zRevRange(string $key, int $start, int $stop, bool $withScores = false): int|bool|array|string
    {
        $result = $this->cmd('ZREVRANGE', $key, $start, $stop, $withScores ? 'WITHSCORES' : '');
        if ($withScores && $result) {
            $data = [];
            $tmp = [];
            foreach ($result as $k => $v) {
                if ($k % 2 === 0) {
                    $tmp['value'] = $v;
                } else {
                    $tmp['score'] = $v;
                    $data[] = $tmp;
                }
            }
            $result = $data;
        }
        return $result;
    }

    /**
     * 返回有序集中，成员的分数值
     * @param string $key
     * @param string $member
     * @return array|bool|int|string
     */
    public function zScore(string $key, string $member): array|bool|int|string
    {
        return $this->cmd('ZSCORE', $key, $member);
    }

    /**
     * 计算给定的一个或多个有序集的并集
     * @param string $destination
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function zUnionStore(string $destination, ...$keys): array|bool|int|string
    {
        $result = $this->cmd('ZUNIONSTORE', $destination, count($keys), implode(' ', $keys));
        return $result;
    }

    /**
     * 迭代有序集合中的元素
     * @param string $key
     * @param int $cursor
     * @param string $pattern
     * @param string $count
     * @return array|bool|int|string
     */
    public function zScan(string $key, int $cursor, string $pattern = '', string $count = ''): array|bool|int|string
    {
        if (!empty($pattern)) {
            $pattern = 'match ' . $pattern;
        }
        if (!empty($count)) {
            $count = 'count ' . $count;
        }
        $result = $this->cmd('ZSCAN', $key, $cursor, $pattern, $count);
        if ($result[1]) {
            $data = [];
            $tmp = [];
            foreach ($result[1] as $k => $v) {
                if ($k % 2 === 0) {
                    $tmp['value'] = $v;
                } else {
                    $tmp['score'] = $v;
                    $data[] = $tmp;
                }
            }
            $result[1] = $data;
        }
        return $result;
    }

    /**
     * 将所有元素参数添加到 HyperLogLog 数据结构中
     * @param string $key
     * @param mixed $elements
     * @return bool
     */
    public function pfAdd(string $key, ...$elements): bool
    {
        $result = $this->cmd('PFADD', $key, implode(' ', $elements));
        return $result === 1;
    }

    /**
     * 返回给定 HyperLogLog 的基数估算值
     * @param mixed $keys
     * @return array|bool|int|string
     */
    public function pfCount(...$keys): array|bool|int|string
    {
        return $this->cmd('PFCOUNT', implode(' ', $keys));
    }

    /**
     * 将多个 HyperLogLog 合并为一个 HyperLogLog
     * @param string $destkey
     * @param mixed $keys
     * @return bool
     */
    public function pfMerge(string $destkey, ...$keys): bool
    {
        $result = $this->cmd('PFMERGE', $destkey, implode(' ', $keys));
        return $this->isOk($result);
    }

    /**
     * 用于取消事务，放弃执行事务块内的所有命令
     * @return bool
     */
    public function disCard(): bool
    {
        if (self::$isMulti) {
            $result = $this->cmd('DISCARD');
            return $this->isOk($result);
        }
        return false;
    }

    /**
     * 是否开启了事务
     * @return bool
     */
    public function isMulti(): bool
    {
        return self::$isMulti;
    }

    /**
     * 开启一个事务
     * @return bool
     */
    public function multi(): bool
    {
        if (self::$isMulti) {
            return true;
        }
        self::$isMulti = true;
        $this->cmd('MULTI');
        return true;
    }

    /**
     * 取消 WATCH 命令对所有 key 的监视
     * @return bool
     */
    public function unWatch(): bool
    {
        if (self::$isMulti) {
            $this->cmd('UNWATCH');
        }
        return true;
    }

    /**
     * 监视一个(或多个) key
     * @param mixed $keys
     * @return bool
     */
    public function watch(...$keys): bool
    {
        $this->cmd('WATCH', implode(' ', $keys));
        return true;
    }

    /**
     * 开启事务
     */
    public function startTrans(): void
    {
        $this->multi();
    }

    /**
     * 提交事务
     */
    public function commit(): void
    {
        $this->exec();
    }

    /**
     * 取消事务
     */
    public function rollback(): void
    {
        $this->disCard();
    }

    /**
     * 转义字符串
     * @param string $data
     * @return string
     */
    public function dealData(string $data): string
    {
        return '\'' . addslashes($data) . '\'';
    }

    /**
     * 去掉转义字符
     * @param string $result
     * @return string
     */
    public function dealResult(string $result): string
    {
        return stripcslashes($result);
    }

    /**
     * 通过密码验证连接到 redis服务
     * @param string $password
     * @return bool
     */
    public function auth(string $password): bool
    {
        $result = $this->cmd('AUTH', $password);
        return $this->isOk($result);
    }

    /**
     * 打印给定的字符串
     * @param string $message
     * @return array|bool|int|string
     */
    public function echo(string $message): array|bool|int|string
    {
        return $this->cmd('ECHO', $message);
    }

    /**
     * 测试与服务器的连接是否仍然生效
     * @return array|bool|int|string
     */
    public function ping(): array|bool|int|string
    {
        return $this->cmd('PING');
    }

    /**
     * 关闭与当前客户端与redis服务的连接
     * @return bool
     */
    public function quit(): bool
    {
        $this->cmd('QUIT');
        return true;
    }

    /**
     * 获取 redis 服务器的统计信息
     * @param string $section
     * @return int|bool|array|string
     */
    public function info($section = ''): int|bool|array|string
    {
        if (empty($section)) {
            $data = [
                'INFO'
            ];
        } else {
            $data = [
                'INFO',
                $section
            ];
        }
        return $this->cmd2($data);
    }

    /**
     * 异步执行一个 AOF（AppendOnly File） 文件重写操作
     * @return array|bool|int|string
     */
    public function bGReWriteAof(): array|bool|int|string
    {
        return $this->cmd('BGREWRITEAOF');
    }

    /**
     * 在后台异步保存当前数据库的数据到磁盘
     * @return array|bool|int|string
     */
    public function bGSave(): array|bool|int|string
    {
        return $this->cmd('BGSAVE');
    }

    /**
     * 用于关闭客户端连接
     * @param string $ip
     * @param int $port
     * @return bool|string
     */
    public function clientKill(string $ip, int $port): bool|string
    {
        $result = $this->cmd('CLIENT', 'KILL', $ip . ':' . $port);
        return $this->isOk($result);
    }

    /**
     * 返回所有连接到服务器的客户端信息和统计数据
     * @return array|bool|int|string
     */
    public function clientList(): array|bool|int|string
    {
        $result = $this->cmd('CLIENT', 'LIST');
        if (!empty($result)) {
            $result = trim($result);
        }
        return $result;
    }

    /**
     * 获取连接设置的名字
     * @return array|bool|int|string
     */
    public function clientGetName(): array|bool|int|string
    {
        return $this->cmd('CLIENT', 'GETNAME');
    }

    /**
     * 设置连接名字
     * @param string $name
     * @return array|bool|int|string
     */
    public function clientSetName(string $name): array|bool|int|string
    {
        return $this->cmd('CLIENT', 'SETNAME', $name);
    }

    /**
     * 用于阻塞客户端命令一段时间（以毫秒计）
     * @param int $timeOut 阻塞时间，单位，毫秒
     * @return bool
     */
    public function clientPause(int $timeOut): bool
    {
        $result = $this->cmd('CLIENT', 'PAUSE', $timeOut);
        return $this->isOk($result);
    }

    /**
     * 用于当前的集群状态，以数组形式展示
     * @return array|bool|int|string
     */
    public function clusterSlots(): array|bool|int|string
    {
        return $this->cmd('CLUSTER', 'SLOTS');
    }

    /**
     * 返回所有的Redis命令的详细信息
     * @return array|bool|int|string
     */
    public function command(): array|bool|int|string
    {
        return $this->cmd('COMMAND');
    }

    /**
     * 用于统计 redis 命令的个数
     * @return array|bool|int|string
     */
    public function commandCount(): array|bool|int|string
    {
        return $this->cmd('COMMAND', 'COUNT');
    }

    /**
     * 用于获取所有 key
     * @param mixed $command 执行命令
     * @return int|bool|array|string
     */
    public function commandGetKeys(...$command): int|bool|array|string
    {
        return $this->cmd2(array_merge([
            'COMMAND',
            'GETKEYS'
        ], $command));
    }

    /**
     * 获取 redis 命令的描述信息
     * @param mixed $command
     * @return int|bool|array|string
     */
    public function commandInfo(...$command): int|bool|array|string
    {
        $result = $this->cmd2(array_merge([
            'COMMAND',
            'INFO'
        ], $command));
        return $result;
    }

    /**
     * 获取 redis 服务的配置参数
     * @param string $config
     * @return array
     */
    public function configGet(string $config): array
    {
        $result = $this->cmd('CONFIG', 'GET', $config);
        return $this->toAssocArr($result);
    }

    /**
     * 对启动 Redis 服务器时所指定的 redis.conf 配置文件进行改写
     * @param string $config
     * @param string $value
     * @return bool
     */
    public function configSet(string $config, string $value): bool
    {
        $result = $this->cmd('CONFIG', 'SET', $config, $value);
        return $this->isOk($result);
    }

    /**
     * 动态地调整 Redis 服务器的配置(configuration)而无须重启
     * @param string $config
     * @param string $value
     * @return bool
     */
    public function configRewrite(string $config, string $value): bool
    {
        $result = $this->cmd('CONFIG', 'SET', $config, $value);
        if ($this->isOk($result)) {
            $result = $this->cmd('CONFIG', 'REWRITE');
        }
        return $this->isOk($result);
    }

    /**
     * 重置 INFO 命令中的某些统计数据
     * @return bool
     */
    public function configReSetStat(): bool
    {
        $this->cmd('CONFIG', 'RESETSTAT');
        return true;
    }

    /**
     * 返回当前数据库的 key 的数量
     * @return array|bool|int|string
     */
    public function dbSize(): array|bool|int|string
    {
        return $this->cmd('DBSIZE');
    }

    /**
     * 调试命令
     * @param string $key
     * @return array|bool|int|string
     */
    public function debugObject(string $key): array|bool|int|string
    {
        return $this->cmd('DEBUG', 'OBJECT', $key);
    }

    /**
     * 清空整个 Redis 服务器的数据(删除所有数据库的所有 key )
     * @return bool
     */
    public function flushAll(): bool
    {
        $this->cmd('FLUSHALL');
        return true;
    }

    /**
     * 最近一次 Redis 成功将数据保存到磁盘上的时间
     * @param bool $format 是否格式化时间
     * @return array|bool|int|string
     */
    public function lastSave(bool $format = true): array|bool|int|string
    {
        $result = $this->cmd('LASTSAVE');
        if (!empty($result) && $format) {
            $result = date('Y-m-d H:i:s', $result);
        }
        return $result;
    }

    /**
     * 查看主从实例所属的角色
     * @return array|bool|int|string
     */
    public function role(): array|bool|int|string
    {
        return $this->cmd('ROLE');
    }

    /**
     * 执行一个同步保存操作，将当前 Redis 实例的所有数据快照(snapshot)以 RDB 文件的形式保存到硬盘
     * @return bool
     */
    public function save(): bool
    {
        $result = $this->cmd('SAVE');
        return $this->isOk($result);
    }

    /**
     * 关闭 redis 服务器
     * @return array|bool|int|string
     */
    public function shutDown(): array|bool|int|string
    {
        return $this->cmd('SHUTDOWN');
    }

    /**
     * 将当前服务器转变为指定服务器的从属服务器(slave server)
     * @param string $host
     * @param int $port
     * @return bool
     */
    public function slaveOf(string $host, int $port): bool
    {
        $this->cmd('SLAVEOF', $host, $port);
        return true;
    }

    /**
     * 查询执行时间的日志系统
     * @param int $num
     * @return array|bool|int|string
     */
    public function slowlogGet(int $num = 1): array|bool|int|string
    {
        return $this->cmd('SLOWLOG', 'GET', $num);
    }

    /**
     * 日志条数
     * @return array|bool|int|string
     */
    public function slowlogLen(): array|bool|int|string
    {
        return $this->cmd('SLOWLOG', 'LEN');
    }

    /**
     * 重置日志
     * @return bool
     */
    public function slowlogReset(): bool
    {
        $result = $this->cmd('SLOWLOG', 'RESET');
        return $this->isOk($result);
    }

    /**
     * 用于同步主从服务器
     * @return array|bool|int|string
     */
    public function sync(): array|bool|int|string
    {
        return $this->cmd('SYNC');
    }

    /**
     * 返回当前服务器时间
     * @return array|bool|int|string
     */
    public function time(): array|bool|int|string
    {
        return $this->cmd('TIME');
    }

    /**
     * 输出 redis安装目录
     * @return array|bool|int|string
     */
    public function configGetDir(): array|bool|int|string
    {
        return $this->cmd('CONFIG', 'GET', 'dir');
    }

    /**
     * 查看是否设置了密码验证
     * @return array|bool|int|string
     */
    public function configGetRequirePass(): array|bool|int|string
    {
        return $this->cmd('CONFIG', 'GET', 'requirepass');
    }

    /**
     * 动态设置redis密码，重启redis将失效
     * @param string $pass redis密码
     * @return bool
     */
    public function configSetRequirePass(string $pass): bool
    {
        $result = $this->cmd('CONFIG', 'SET', 'requirepass', $pass);
        return $this->isOk($result);
    }

    /**
     * 获取最大连接数
     * @return array|bool|int|string
     */
    public function configGetMaxClients(): array|bool|int|string
    {
        return $this->cmd('CONFIG', 'SET', 'maxclients');
    }
}