**安装memcached** 

以linux操作系统为例

参考链接：<https://memcached.org/downloads>

① 安装libevent-dev

Debian/Ubuntu:

`apt-get install libevent-dev` 

Redhat/Centos

 `yum install libevent-devel`

② 安装memcached

```linux
wget http://memcached.org/latest
tar -zxvf memcached-1.x.x.tar.gz
cd memcached-1.x.x
./configure && make && make test && sudo make install
```

启动memcached

`service memcached start`

停止memcached

`service memcached stop`

**php使用memcached**

无需安装memcached扩展，即可使用

快速开始

```php
<?php
namespace application\home\controller;

use library\mysmarty\Memcached;

class Index
{

    public function test()
    {
        Memcached::getInstance()->set('name', '中国');
        var_dump(Memcached::getInstance()->get('name'));
    }
}
```

结果

```php
string(6) "中国"
```

