要使用smarty模板技术，需要控制器继承Controller，就可以使用smarty模板提供的所有方法

`\application\home\controller\Index.php`

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
}
```

模板文件

`\application\home\view\index\test.html`

```php+HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
</head>
<body>
您好，中国
</body>
</html>
```

默认模板文件目录，`\application\模块名\view\控制器名`

模板文件名为方法名，文件后缀为html

您也可以使用其它文件名，smarty默认模板目录指向了 `\application\模块名\view` 目录

```php
<?php
namespace application\home\controller;
use my\library\SmartyController;

class Index extends Controller{
    public function test(){
        $this->assign('words','您好，中国');
        $this->display('index/filetest.html');
    }
}
```

使用 index 控制器下的 filetest.html 模板文件

**渲染模板方法说明**

假如模板文件 `\application\home\view\index\test.html`

与模板文件命令规则一致

```php
$this->display();
```

```php
$this->view();
```

指定自定义模板文件

```php
$this->display('index/test.html');
```

使用view方法，不需要指定模板文件后缀名

```php
$this->view('index.test');
```

