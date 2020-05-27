在项目根目录下创建 `composer.json` 文件

以安装 `predis` 扩展库为例，则 `composer.json` 内容如下

```json
{
  "require": {
    "predis/predis": "1.*"
  }
}
```

然后用 composer 安装

参考链接：<https://getcomposer.org/doc/01-basic-usage.md#package-versions>

**测试 predis** 

```php
<?php

namespace application\home\controller;

use Predis\Client;

class Index
{

    public function test()
    {
        $client = new Client();
        $client->set('foo', 'bar');
        $value = $client->get('foo');
        var_dump($value);
    }
}
```

```php
string(3) "bar"
```

