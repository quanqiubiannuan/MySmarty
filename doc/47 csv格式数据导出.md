导出数据到表格，支持超大数据量导出。

```php
<?php

namespace application\home\controller;


use library\mysmarty\Controller;
use library\mysmarty\Csv;

class Index extends Controller
{
    public function test()
    {
        // 开始一个实例
        $csv = new Csv();
        // 定义好导出文件名以及表头
        $csv->startCsv('测试数据导出',[
            '姓名',
            '年龄'
        ]);
        // 添加数据
        for ($i=0;$i<30;$i++){
            $csv->putCsv([
                '张三'.$i,
                random_int(10,50)
            ]);
        }
        // 结束添加数据
        $csv->endCsv();
    }
}
```

