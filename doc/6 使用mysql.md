**安装mysql**

软件地址：<https://dev.mysql.com/downloads/mysql/8.0.html>

配置数据库连接信息

`config/database.php`

```php
'mysql' => [
 	//主机ip
 	'host' => '127.0.0.1',
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
]
```

**开始使用**

使用Db类查询

```php
<?php
namespace application\home\controller;
use library\mysmarty\Db;

class Index{
    public function test(){
        //普通查询
        var_dump(Db::query('select * from user'));
        //绑定参数
        var_dump(Db::query('select * from user where m_id = ?',[1]));
        //添加数据
        var_dump(Db::execute("INSERT INTO `test`.`user` (`student_id`) VALUES ('212')"));
        var_dump(Db::execute("INSERT INTO `test`.`user` (`student_id`) VALUES ('?')",[212]));
    }
}
```

使用其它数据库连接查询（不同的数据库服务器）

```php
var_dump(Db::connect('mysql2')->table('user')->find());
```

需要在 `\application\database.php` 配置数据库连接信息

```php
'mysql2' => [
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
]
```

**使用模型**

创建模型文件，比如user表对应的模型文件为User.php，user_info 表对应的模型文件为 UserInfo.php

模型

```
\application\home\model\User.php
```

```php
<?php

namespace application\home\model;

use library\mysmarty\Model;

class User extends Model
{
}
```

控制器

```
\application\home\controller\Index.php
```

```php
<?php

namespace application\home\controller;

use application\home\model\User;

class Index
{
    public function test()
    {
        $user = new User();
        var_dump($user->find());
    }
}
```

您也可以在模型文件中指定表名，数据库名

```php
<?php

namespace application\home\model;

use library\mysmarty\Model;

class User extends Model
{

    protected $database = 'test';

    protected $table = 'user';
}
```

没有指定表名的话，将使用类名的小写字母（多个大写单词的将用 _ 分隔）作为表名，所以模型名一定要按照规则来命名

**使用获取器**

模型

```php
<?php
namespace application\home\model;
use library\mysmarty\Model;
class User extends Model{
    protected $database = 'test';
    protected $table = 'user';
    
    function getMIdAttr($value){
        //转为整型数据
        return intval($value); 
    }
}
```

获取 m_id 字段的值，需要添加 getMIdAttr 方法，$value 为原始数据库中的值，返回您修改后的值。

控制器

```php+HTML
<?php
namespace application\home\controller;
use application\home\model\User;
class Index{
    public function test(){
        $user = new User();
        var_dump($user->where('m_id',1)->getAttr('m_id')->find());
    }
}
```

使用 getAttr 方法，将需要获取的字段以逗号分割传入即可

**使用修改器**

模型

```php
<?php
namespace application\home\model;
use library\mysmarty\Model;
class User extends Model{
    protected $database = 'test';
    protected $table = 'user';
    
    function getMIdAttr($value){
        //转为整型数据
        return intval($value); 
    }
    
    function setMIdAttr($value){
        //转为整型数据
        return intval($value); 
    }
}
```

控制器

```php
<?php
namespace application\home\controller;
use application\home\model\User;
class Index{
    public function test(){
        $user = new User();
        var_dump($user->where('m_id',1)->getAttr('m_id')->find());
        var_dump($user->setAttr('m_id')->add([
            'm_id' => '100.23'
        ]));
    }
}
```

**类型转换**

支持给字段设置类型自动转换，会在写入和读取的时候自动进行类型转换处理，例如：

```php
<?php
namespace application\home\model;

use library\mysmarty\Model;

class Node extends Model
{
    
    protected $database = 'bbs';
    
    protected $table = 'node';
   
    
    protected $type = [
        'id' => 'integer',
        'p_id' => 'integer',
        'name' => 'array'
    ];
}
```

```php
<?php
namespace application\home\controller;

use application\home\model\Node;

class Index
{

    public function test()
    {
        $node = new Node();
        
        $node->add([
            'name' => [
                'a' => 12
            ],
            'p_id' => 1
        ]);
        
        $result = $node->select();
        var_dump($result);
    }
}
```

结果

```php

array(1) {
  [0]=>
  array(4) {
    ["id"]=>
    int(1)
    ["name"]=>
    array(1) {
      ["a"]=>
      int(12)
    }
    ["p_id"]=>
    int(1)
    ["status"]=>
    string(1) "0"
  }
}
```

integer

设置为integer（整型）后，该字段写入和输出的时候都会自动转换为整型。

float

该字段的值写入和输出的时候自动转换为浮点型。

boolean

该字段的值写入和输出的时候自动转换为布尔型。

array

如果设置为强制转换为array类型，系统会自动把数组编码为json格式字符串写入数据库，取出来的时候会自动解码。

object

该字段的值在写入的时候会自动编码为json字符串，输出的时候会自动转换为stdclass对象。

serialize

指定为序列化类型的话，数据会自动序列化写入，并且在读取的时候自动反序列化。

timestamp

指定为时间戳字段类型的话，该字段的值在写入时候会自动使用strtotime生成对应的时间戳，输出的时候会自动转换为dateFormat属性定义的时间字符串格式，默认的格式为Y-m-d H:i:s

datetime

和timestamp类似，区别在于写入和读取数据的时候都会自动处理成时间字符串Y-m-d H:i:s的格式。

**更多方法请参考编辑器代码提示**

