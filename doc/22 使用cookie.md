控制器

```php
<?php

namespace application\home\controller;

use library\mysmarty\Controller;
use library\mysmarty\Cookie;

class Index extends Controller
{
    public function test()
    {
        var_dump(Cookie::getInstance()->set('name', 'mysmarty'));
        var_dump(Cookie::getInstance()->get('name'));
        var_dump(Cookie::getInstance()->get('name2'));
        var_dump(Cookie::getInstance()->delete('name'));
        var_dump(Cookie::getInstance()->get('name', '李四'));
        Cookie::getInstance()->clear();
        var_dump(Cookie::getInstance()->get('name', '李四'));
    }
}
```