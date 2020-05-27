**安装Redis**

如果您的服务器是新购的，记得执行下 `yum update` 更新下软件

Redis下载地址： https://redis.io/download 

```
wget http://download.redis.io/releases/redis-5.0.7.tar.gz
```

```
tar xzf redis-5.0.7.tar.gz
```

```
cd redis-5.0.7
```

```
make
```

```
make test
```

```
make install
```

**开机自动启动**

```
cd utils/
```

```
./install_server.sh
```

一路按enter回车键即可。

配置文件在：/etc/redis/6379.conf

一般需要修改Redis绑定的IP（默认是127.0.0.1，即不支持外网访问的），Redis密码，Redis最大使用内存。

下面是配置的一些相关信息

```
# 1k => 1000 bytes
# 1kb => 1024 bytes
# 1m => 1000000 bytes
# 1mb => 1024*1024 bytes
# 1g => 1000000000 bytes
# 1gb => 1024*1024*1024 bytes
```

监听ip

```
bind 127.0.0.1
```

监听端口

```
port 6379
```

```
maxmemory <bytes>
```

内存设置，注意单位

```
maxmemory 1024
```

设置密码

```
requirepass 123456
```

**测试Redis**

执行：`redis-cli`

```
127.0.0.1:6379> ping
PONG
127.0.0.1:6379> 
```

输入ping 返回pong则代表安装成功！

**安装过程中出错**

①  /bin/sh: cc: command not found

执行 ： `yum -y install gcc gcc-c++ libstdc++-devel` 

② zmalloc.h:50:31: fatal error: jemalloc/jemalloc.h: No such file or directory

执行：`make MALLOC=libc`

③ You need tcl 8.5 or newer in order to run the Redis test

执行：`yum install tcl`

**无需php安装redis扩展，但需要您安装redis软件**

配置redis

编辑 `config/database.php` 文件

```php
'redis' => [
 	//主机ip
 	'host' => '127.0.0.1',
 	//redis 端口
 	'port' => 6379,
 	//redis 密码
 	'pass' => ''
]
```

控制器

```php
<?php

namespace application\home\controller;


use library\mysmarty\Redis;

class Index
{
    public function test()
    {
        //选择库
        Redis::getInstance()->select(1);
        //存储值
        Redis::getInstance()->set('name', '李小姐', 3600);
        //获取值
        var_dump(Redis::getInstance()->get('name'));
        //删除值
        Redis::getInstance()->del('name');
        //使用通用方法,命令以数组方式传入
        Redis::getInstance()->cmd('del', 'name');
        //下面方法需要自行转义有空格的值导致执行失败
        Redis::getInstance()->exec('del name');
    }
}
```

更多方法参考类代码提示！

