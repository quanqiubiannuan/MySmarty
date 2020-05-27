确保已经安装 `sqlite3` 扩展

```shell
[root@localhost doc]# php -m
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

**配置sqlite**

`config/database.php`

```php
'sqlite' => [
 	//数据存放目录
 	'databaseDir' => ROOT_DIR . '/sqlite',
 	//默认数据库
 	'database' => 'test',
 	//默认表
 	'table' => 'my'
 ]
```

**创建表**

```php
<?php

namespace application\home\controller;


use library\mysmarty\Sqlite;

class Index
{
    public function test()
    {
        $sql = <<<SQL
        create table user(
            id INT PRIMARY KEY NOT NULL,
            name VARCHAR(50) NOT NULL,
            age INT NOT NULL
        );
SQL;
        var_dump(Sqlite::getInstance()->exec($sql));
    }
}
```

```php
bool(true)
```

```
exec 执行sql语句，true 执行成功，false 执行失败
```

创建info表

```php
<?php

namespace application\home\controller;


use library\mysmarty\Sqlite;

class Index
{
    public function test()
    {
        $sql = <<<SQL
 	create table info(
 		id INT PRIMARY KEY NOT NULL,
 		user_id INT NOT NULL,
 		schoole VARCHAR(50) NOT NULL
 	);
SQL;
        var_dump(Sqlite::getInstance()->exec($sql));
    }
}
```

**添加数据**

```php
var_dump(Sqlite::getInstance()->table('user')->insert([
 	'id' => 1,
 	'name' => '张三',
 	'age' => 15
]));
```

```php
var_dump(Sqlite::getInstance()->table('info')->insert([
 	'id' => 1,
 	'user_id' => 1,
  	'schoole' => '清华大学'
]));
```

**查询数据**

```php
var_dump(Sqlite::getInstance()->table('user')->select());
var_dump(Sqlite::getInstance()->table('info')->find());
```

查询所有

```php
var_dump(Sqlite::getInstance()->table('user')->select());
```

查询名字为张三的数据

```php
var_dump(Sqlite::getInstance()->table('user')->eq('name','张三')->select());
```

查询id为1的数据

```php
var_dump(Sqlite::getInstance()->table('user')->where('id',1)->find());
```

连表查询

```php
var_dump(Sqlite::getInstance()->table('user')->field('user.*,info.schoole')->outerJoin('info','info.user_id=user.id')->where('user.id',1)->find());
```

**更新数据**

```php
var_dump(Sqlite::getInstance()->table('user')->where('id',1)->update([
    'name' => '李四'
]));
```

**删除数据**

```php
var_dump(Sqlite::getInstance()->table('user')->where('id',1)->delete());
```

清空表数据

```php
var_dump(Sqlite::getInstance()->truncate('info'));
```

