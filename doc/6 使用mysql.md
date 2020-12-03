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

创建数据表

学校表

```sql
CREATE TABLE `school` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NULL DEFAULT '',
	`create_time` DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB;
```

学生表

```sql
CREATE TABLE `student` (
	`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NULL DEFAULT '',
	`age` TINYINT(3) UNSIGNED NULL DEFAULT '0',
	`school_id` INT(10) UNSIGNED NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
```

使用Db类查询

```php
<?php
namespace application\home\controller;
use library\mysmarty\Db;

class Index{
    public function test(){
     	//普通查询
        var_dump(Db::query('select * from student'));
        //绑定参数
        var_dump(Db::query('select * from student where id = ?',[1]));
        //添加数据
        var_dump(Db::execute("INSERT INTO `test`.`student` (`name`) VALUES ('212')"));
        var_dump(Db::execute("INSERT INTO `test`.`student` (`name`) VALUES (?)",[212]));
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
application/home/model/Student.php
```

```php
<?php
namespace application\home\model;

use library\mysmarty\Model;

class Student extends Model{

}
```

控制器

```
\application\home\controller\Index.php
```

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->find());
    }
}
```

您也可以在模型文件中指定表名，数据库名

```php
<?php

namespace application\home\model;

use library\mysmarty\Model;

class Student extends Model
{
    protected string $database = 'test';
    protected string $table = 'student';
}
```

没有指定表名的话，将使用类名的小写字母（多个大写单词的将用 _ 分隔）作为表名，所以模型名一定要按照规则来命名

**使用获取器**

模型

```php
<?php

namespace application\home\model;

use library\mysmarty\Model;

class Student extends Model
{
    protected string $database = 'test';
    protected string $table = 'student';

    public function getNameAttr(string $value): string
    {
        return $value . '修改';
    }
}
```

获取 m_id 字段的值，需要添加 getMIdAttr 方法，$value 为原始数据库中的值，返回您修改后的值。

控制器

```php+HTML
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->getAttr('name')->find());
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

class Student extends Model
{
    protected string $database = 'test';
    protected string $table = 'student';

    public function getNameAttr(string $value): string
    {
        return $value . '修改';
    }

    public function setNameAttr(string $value): string
    {
        return $value . '添加';
    }
}
```

控制器

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->setAttr('name')->add([
            'name' => '哈哈'
        ]));
    }
}
```

**使用添加器**

一般用在查询数据上，根据其它字段来添加一个数据库表没有的字段显示。

模型

```php
<?php

namespace application\home\model;

use library\mysmarty\Model;

class Student extends Model
{
    protected string $database = 'test';
    protected string $table = 'student';

    public function getNameAttr(string $value): string
    {
        return $value . '修改';
    }

    public function setNameAttr(string $value): string
    {
        return $value . '添加';
    }

    public function addSexAttr(array $data): string
    {
        if ($data['age']) {
            return '男';
        }
        return '女';
    }
}
```

控制器

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->addAttr('sex')->find());
    }
}
```

**类型转换**

默认查出来的数据均为字符串类型！

支持给字段设置类型自动转换，会在写入和读取的时候自动进行类型转换处理，例如：

```php
<?php

namespace application\home\model;

use library\mysmarty\Model;

class Student extends Model
{
    protected string $database = 'test';
    protected string $table = 'student';
    protected array $type = [
        'age' => 'integer'
    ];

    public function getNameAttr(string $value): string
    {
        return $value . '修改';
    }

    public function setNameAttr(string $value): string
    {
        return $value . '添加';
    }

    public function addSexAttr(array $data): string
    {
        if ($data['age']) {
            return '男';
        }
        return '女';
    }
}
```

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->find());
    }
}
```

结果

```php

array(4) {
  ["id"]=>
  string(1) "1"
  ["name"]=>
  string(3) "222"
  ["age"]=>
  float(0)
  ["school_id"]=>
  string(1) "0"
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

**添加数据**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->add([
            'name' => '李四',
            'age' => 20
        ]));
    }
}
```

返回结果为自增ID，如果表没有自增ID，则返回受影响的行数。

还可以使用insert方法

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->insert([
            'name' => '李四',
            'age' => 20
        ]));
    }
}
```

对添加的数据进行字段过滤

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->allowField(true)->insert([
            'name' => '李四',
            'age' => 20,
            'sex' => 1
        ]));
    }
}
```

**查询数据**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->where('id',1)->find());
    }
}
```

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->where('id',1,'>')->find());
        var_dump($student->where('id',1,'!=')->find());
        var_dump($student->where('id',1,'<')->find());
    }
}
```

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->eq('id','1')->find());
        var_dump($student->neq('id','1')->find());
        var_dump($student->gt('id','1')->find());
        var_dump($student->lt('id',8)->find());
        var_dump($student->like('name','%四%')->find());
    }
}
```

**join查询**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->leftJoin('school','school.id=student.school_id')->find());
    }
}
```

**关联查询**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->with('school','id','school_id')->find());
    }
}
```

**更新数据**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->eq('id', 1)->update([
            'name' => '张三'
        ]));

        var_dump($student->gt('id', 1)->update([
            'age' => 34
        ]));

        var_dump($student->eq('id', 2)->replace([
            'age' => 100
        ]));
    }
}
```

**删除数据**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->eq('id', '12')->delete());
        var_dump($student->delete(1));
        var_dump($student->delete([
            'id' => 2
        ]));
    }
}
```

**聚合查询**

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->count());
        var_dump($student->avg('age'));
        var_dump($student->sum('age'));
        var_dump($student->min('age'));
        var_dump($student->max('id'));
    }
}
```

还有解决不了的sql操作问题，请使用原生sql操作

```php
<?php

namespace application\home\controller;

use application\home\model\Student;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test()
    {
        $student = new Student();
        var_dump($student->query('select * from student'));
        var_dump($student->execute('delete from student where id =2'));
    }
}
```

query 用于查询语句

execute 用于添加、更新、删除