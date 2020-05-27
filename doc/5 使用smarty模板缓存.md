打开缓存，编辑 `config/smarty.php` 文件

```php
//强制编译，线上环境最好设置为false。开启缓存时，必须设置为false
'force_compile' => false
//缓存开启，0 关闭，1 开启，2 单独配置每个页面的缓存
'cache' => 1,
/**
* 自定义缓存存储方式
* 为空，使用文件缓存
* mysql，使用mysql作为缓存存放位置
* redis，使用redis作为缓存存放位置
* elasticsearch，使用elasticsearch作为缓存存放位置
*/
'caching_type' => '',
//自定义缓存配置参数
'caching_type_params' => [
	//mysql缓存配置
	'mysql' => [
        //数据库
        'database' => 'test',
        //缓存表
        'table' => 'cache'
	],
	//redis缓存配置
   	'redis' => [
		//使用第几个库（0 - 15）
		'db' => 0
	],
    // elasticsearch 缓存配置
 	'elasticsearch' => [
 		// 默认 数据库，索引
 		'database' => 'mycache',
 		// 默认 表，文档
 		'table' => 'cache'
 	]
],
//缓存时间,单位秒
'cache_life_time' => 3600
```

使用 Redis 或 MySQL 作为缓存位置时，需要配置相应的账号与密码

使用mysql作为缓存需要创建相应的表

```sql
CREATE TABLE `cache` (
	`id` CHAR(40) NOT NULL,
	`name` VARCHAR(250) NOT NULL,
	`cache_id` VARCHAR(250) NULL DEFAULT NULL,
	`compile_id` VARCHAR(250) NULL DEFAULT NULL,
	`modified` DATETIME NOT NULL,
	`content` LONGTEXT NOT NULL,
	PRIMARY KEY (`id`),
	INDEX `name` (`name`),
	INDEX `cache_id` (`cache_id`),
	INDEX `compile_id` (`compile_id`),
	INDEX `modified` (`modified`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
```

使用elasticsearch作为缓存需要创建相应的表（文档）

```php
public function create()
{
 	$result = ElasticSearch::getInstance()->setDataBase('mycache')
            ->setTable('cache')
            ->setProperty('id')
            ->setProperty('name')
            ->setProperty('cache_id')
            ->setProperty('compile_id')
            ->setProperty('modified', 'integer')
            ->setProperty('content')
            ->createDataBaseByMapping();
 	var_dump($result);
}
```

`\application\database.php`

```php
<?php
namespace application;
/**
 * 数据库配置
 */
return [
    /**
     * 不同的数据库可以配置不同的名称，mysql为默认连接名称
     */
    'mysql' => [
        //主机ip
        'host' => '192.168.26.77',
        //mysql 用户名
        'user' => 'root',
        //mysql 密码
        'password' => 'root',
        //mysql 端口
        'port' => 3306,
        //mysql 默认数据库
        'database' => 'test',
        //mysql 字符编码
        'charset' => 'utf8'
    ],
    'redis' => [
        //主机ip
        'host' => '127.0.0.1',
        //redis 端口
        'port' => 6379,
        //redis 密码
        'pass' => '123456'
    ],
    'elasticsearch' => [
        //协议
        'protocol' => 'http',
        //主机ip
        'ip' => '127.0.0.1',
        //端口
        'port' => 9200
    ]
];
```

使用文件作为缓存位置，无需任何配置，只需要确保 \runtime 目录有可写权限

**测试smarty缓存**

```php
<?php

namespace application\home\controller;


use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $this->display();
    }

    public function test2(){
        if (!$this->hasCached()){
            var_dump('分配数据');
            $this->assign('time',date('Y-m-d H:i:s'));
        } else {
            var_dump('不需要分配数据，使用缓存');
        }
        $this->display();
    }
}
```

您还可以使用下面的方法使用缓存，前提是模板文件的位置按框架的规范放置！

无需指定模板文件，只需要给定一个缓存id即可

```php
<?php

namespace application\home\controller;

use library\mysmarty\Controller;

class Index extends Controller
{

    public function test()
    {
        if (!$this->hasCached(101)) {
            echo '没有缓存';
        }
        $this->showCached(101);
    }
}
```

使用smarty自带的缓存函数，需要您自行判断文件是否缓存了。

您也可以使用局部缓存

```php
<?php

namespace application\home\controller;


use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        if (!$this->hasCached()){
            //需要缓存的数据
            $this->assign('time',date('Y-m-d H:i:s'));
        }
        //不缓存的数据
        $this->assign('num',random_int(100,999));
        $this->display();
    }
}
```

模板文件代码

```html
缓存的数据{$time}
<hr>
不缓存的数据：{nocache}{$num}{/nocache}
```

如果想整个控制器不使用缓存，可设置该控制器的`$myCache`为false

```php
<?php

namespace application\home\controller;

use library\mysmarty\Controller;

class Index extends Controller
{
    //该控制器所有方法不用缓存
    protected $myCache = false;
    
    public function test()
    {
        if (!$this->hasCached()){
            //需要缓存的数据
            $this->assign('time',date('Y-m-d H:i:s'));
        }
        //不缓存的数据
        $this->assign('num',random_int(100,999));
        $this->display();
    }

}
```

