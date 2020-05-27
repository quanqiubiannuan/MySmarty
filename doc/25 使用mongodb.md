**确认mongodb驱动安装没？**

控制台下输入 `php -m`，查看是否有mongodb扩展库

```linux
[root@localhost ~]# php -m
[PHP Modules]
bz2
calendar
Core
ctype
curl
date
dom
exif
fileinfo
filter
ftp
gd
gettext
gmp
hash
iconv
json
libxml
mbstring
mongodb
mysqli
openssl
pcntl
pcre
PDO
pdo_mysql
pdo_sqlite
Phar
posix
readline
Reflection
session
shmop
SimpleXML
sockets
SPL
sqlite3
standard
swoole
sysvmsg
sysvsem
sysvshm
tokenizer
wddx
xml
xmlreader
xmlwriter
xsl
Zend OPcache
zip
zlib

[Zend Modules]
Zend OPcache
```

没有则安装驱动

参考：<https://www.php.net/manual/zh/mongodb.installation.php>

**安装mongodb软件**

参考教程：https://www.runoob.com/mongodb/mongodb-linux-install.html

**mongodb配置**

`config/database.php`

```php
// 配置参考 https://www.php.net/manual/zh/mongodb-driver-manager.construct.php
 'mongodb' => [
 	// mongodb连接uri
 	'uri' => 'mongodb://127.0.0.1/',
 	// 连接uri配置，会覆盖uri参数
 	'uriOptions' => [],
 	// 驱动配置
 	'driverOptions' => [],
 	// 默认数据库
 	'database' => 'test',
 	// 默认数据库表/集合
 	'table' => 'my'
]
```

**使用**

`需要先运行MongoDb软件`

```php
<?php
namespace application\home\controller;

use library\mysmarty\Mongodb;

class Index
{

    public function test()
    {
        //添加数据
        var_dump(Mongodb::getInstance()->add([
            'name' => '23232323',
            'x' => rand(1, 200)
        ]));
        
        //查询数据
        var_dump(Mongodb::getInstance()->where('x', 100, '>')
            ->fieldType('x', 16)
            ->limit(5)
            ->order('x', 'asc')
            ->select());
        
        //更新数据
        var_dump(Mongodb::getInstance()->update([
            'name' => '测试666'
        ], [
            'x' => 128
        ]));
        
        //删除数据
        var_dump(Mongodb::getInstance()->delete([
            'x' => 132
        ]));
    }
}
```

**结果**

```php
bool(true)
array(0) {
}
bool(true)
bool(true)
```

多刷新几次

```php

bool(true)
array(4) {
  [0]=>
  array(3) {
    ["_id"]=>
    string(24) "5d1c9d129dc6d684025122d2"
    ["name"]=>
    string(8) "23232323"
    ["x"]=>
    int(138)
  }
  [1]=>
  array(3) {
    ["_id"]=>
    string(24) "5d1c9d0d9dc6d63a922892b2"
    ["name"]=>
    string(8) "23232323"
    ["x"]=>
    int(167)
  }
  [2]=>
  array(3) {
    ["_id"]=>
    string(24) "5d1c9d0e9dc6d63a93692e02"
    ["name"]=>
    string(8) "23232323"
    ["x"]=>
    int(171)
  }
  [3]=>
  array(3) {
    ["_id"]=>
    string(24) "5d1c9d139dc6d63a94126f93"
    ["name"]=>
    string(8) "23232323"
    ["x"]=>
    int(189)
  }
}
bool(true)
bool(true)
```

