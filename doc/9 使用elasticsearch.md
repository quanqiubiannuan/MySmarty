**安装elasticsearch软件**

下载地址：https://www.elastic.co/downloads/elasticsearch

下面是centos安装6.3.2方法

elasticsearch-6-3-2：https://www.elastic.co/downloads/past-releases/elasticsearch-6-3-2

https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-6.3.2.rpm

安装java

`yum install java`

确认Java是否安装成功

`java -version`

安装elasticsearch

`rpm -ivh elasticsearch-6.3.2.rpm`

设置为开机自启

`systemctl daemon-reload`

`systemctl enable elasticsearch.service`

启动elasticsearch

`systemctl start elasticsearch.service`

设置elasticsearch最大内存

elasticsearch配置文件在`/etc/elasticsearch`

jvm.options

```
# Xms represents the initial size of total heap space
# Xmx represents the maximum size of total heap space
-Xms300m
-Xmx300m
```

elasticsearch.yml

```
# Lock the memory on startup:
#
bootstrap.memory_lock: true
```

参考：https://www.elastic.co/guide/cn/elasticsearch/guide/current/heap-sizing.html

https://www.elastic.co/guide/en/elasticsearch/reference/current/important-settings.html

重新启动

`/etc/init.d/elasticsearch restart`

**配置elasticsearch**

编辑 `config/database.php` 文件

```php
'elasticsearch' => [
 	// 协议
 	'protocol' => 'http',
 	// 主机ip
 	'ip' => '127.0.0.1',
 	// 端口
 	'port' => 9200,
 	// 默认 数据库，索引
 	'database' => 'test',
 	// 默认 表，文档
	'table' => 'my'
]
```

**测试**

控制器

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        //创建 databasename 索引 tablename2 文档 包含 content 一个字段
        $result = ElasticSearch::getInstance()->setDataBase('databasename')
            ->setTable('tablename2')
            ->setProperty('content', 'text')
            ->createDataBaseByMapping();

        var_dump($result);

        // 获取 databasename 索引 tablename2 文档  结构
        var_dump(ElasticSearch::getInstance()->setDataBase('databasename')
            ->setTable('tablename2')
            ->getMapping());
    }
}
```

结果

```php
bool(true)
array(1) {
  ["databasename"]=>
  array(1) {
    ["mappings"]=>
    array(1) {
      ["tablename2"]=>
      array(1) {
        ["properties"]=>
        array(1) {
          ["content"]=>
          array(1) {
            ["type"]=>
            string(4) "text"
          }
        }
      }
    }
  }
}
```

添加数据

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {

        $result = ElasticSearch::getInstance()->setDataBase('databasename')
            ->setTable('tablename2')
            ->insert([
                'content' => 'hello world'
            ]);

        var_dump($result);

        $result = ElasticSearch::getInstance()->setDataBase('databasename')
            ->setTable('tablename2')
            ->insert([
                'content' => '中国'
            ]);

        var_dump($result);
    }
}
```

结果

```php
int(1)
int(1)
```

查询数据

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {

        var_dump(ElasticSearch::getInstance()->setDataBase('databasename')
            ->setTable('tablename2')
            ->select());
    }
}
```

结果

```php
array(2) {
  [0]=>
  array(1) {
    ["content"]=>
    string(11) "hello world"
  }
  [1]=>
  array(1) {
    ["content"]=>
    string(6) "中国"
  }
}
```

**使用中文分词索引数据**

分词插件：https://github.com/medcl/elasticsearch-analysis-ik

安装

`/usr/share/elasticsearch/bin/elasticsearch-plugin install https://github.com/medcl/elasticsearch-analysis-ik/releases/download/v6.3.2/elasticsearch-analysis-ik-6.3.2.zip`

本地文件安装：`./bin/elasticsearch-plugin install file:///root/elasticsearch-analysis-ik-6.3.2.zip`

重启

`/etc/init.d/elasticsearch restart`

测试

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        // 使用 默认的 索引， 默认的 文档，创建包含 id,title,content 3个字段
        $result = ElasticSearch::getInstance()->setProperty('content', 'text', 'ik_max_word', 'ik_max_word')
            ->setProperty('title', 'text', 'ik_max_word', 'ik_max_word')
            ->setProperty('id', 'integer')
            ->createDataBaseByMapping();

        var_dump($result);
    }
}
```

结果

```php
bool(true)
```

获取文档结构

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        // 获取 创建的 文档 结构
        var_dump(ElasticSearch::getInstance()->getMapping());
    }
}
```

结果

```php

array(1) {
  ["test"]=>
  array(1) {
    ["mappings"]=>
    array(1) {
      ["my"]=>
      array(1) {
        ["properties"]=>
        array(3) {
          ["content"]=>
          array(2) {
            ["type"]=>
            string(4) "text"
            ["analyzer"]=>
            string(11) "ik_max_word"
          }
          ["id"]=>
          array(1) {
            ["type"]=>
            string(7) "integer"
          }
          ["title"]=>
          array(2) {
            ["type"]=>
            string(4) "text"
            ["analyzer"]=>
            string(11) "ik_max_word"
          }
        }
      }
    }
  }
}
```

添加数据

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->insert([
            'id' => 1,
            'title' => '比伯宣布暂停音乐事业 海莉评论我爱你表示支持',
            'content' => '3月26日，比伯宣布了他将暂停他的音乐事业，把注意力转移到更重要的事情上，他对歌迷说：“音乐对我来说很重要，但没有什么比我的家庭和健康更重要。无论我是否在音乐巅峰我都会在此停留，但我会回来相信我。”海莉也在这条状态下评论：“没错！我爱你！”'
        ]);

        var_dump($result);

        $result = ElasticSearch::getInstance()->insert([
            'id' => 2,
            'title' => '采育公交快线3月30日起开通 将打造京沪高速进城快线',
            'content' => '中途路由：由采育公交场站发出，经南北大街、育林街、福源路、采林路、育圣街、采和路、育英街、采伟路、采林路、京沪高速至十里河桥南。'
        ]);

        var_dump($result);

        $result = ElasticSearch::getInstance()->insert([
            'id' => 3,
            'title' => '公交集团周末拟开通扫墓专线',
            'content' => '宜昌公交集团表示，为方便市民扫墓、踏青，启动了节假日客流高峰运输预案，拟于本周末开通长江市场至夷陵区长陵园公墓、金东山小商品城至窑湾殡仪馆东山公墓两条扫墓专线。同时，对途经几个公墓的公交线路予以密切关注，根据客流情况及时增加班次，“根据近半个月的运营情况，工作日客流出行时间主要集中在上午7:00－9:30，周末客流主要集中每天上午7:00—12:00，线路日均客流较往常增加2000人次左右。除正常增加班次外，公交集团还根据客流出行特点灵活调度，确保线路正常运营，为市民扫墓出行提供保障。”'
        ]);

        var_dump($result);
    }
}
```

结果

```php
int(1)
int(1)
int(1)
```

查询数据

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->select();
        var_dump($result);
    }
}
```

结果

```php

array(3) {
  [0]=>
  array(3) {
    ["id"]=>
    int(2)
    ["title"]=>
    string(70) "采育公交快线3月30日起开通 将打造京沪高速进城快线"
    ["content"]=>
    string(192) "中途路由：由采育公交场站发出，经南北大街、育林街、福源路、采林路、育圣街、采和路、育英街、采伟路、采林路、京沪高速至十里河桥南。"
  }
  [1]=>
  array(3) {
    ["id"]=>
    int(1)
    ["title"]=>
    string(64) "比伯宣布暂停音乐事业 海莉评论我爱你表示支持"
    ["content"]=>
    string(354) "3月26日，比伯宣布了他将暂停他的音乐事业，把注意力转移到更重要的事情上，他对歌迷说：“音乐对我来说很重要，但没有什么比我的家庭和健康更重要。无论我是否在音乐巅峰我都会在此停留，但我会回来相信我。”海莉也在这条状态下评论：“没错！我爱你！”"
  }
  [2]=>
  array(3) {
    ["id"]=>
    int(3)
    ["title"]=>
    string(39) "公交集团周末拟开通扫墓专线"
    ["content"]=>
    string(696) "宜昌公交集团表示，为方便市民扫墓、踏青，启动了节假日客流高峰运输预案，拟于本周末开通长江市场至夷陵区长陵园公墓、金东山小商品城至窑湾殡仪馆东山公墓两条扫墓专线。同时，对途经几个公墓的公交线路予以密切关注，根据客流情况及时增加班次，“根据近半个月的运营情况，工作日客流出行时间主要集中在上午7:00－9:30，周末客流主要集中每天上午7:00—12:00，线路日均客流较往常增加2000人次左右。除正常增加班次外，公交集团还根据客流出行特点灵活调度，确保线路正常运营，为市民扫墓出行提供保障。”"
  }
}

```

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->where('id', 2)->find();
        var_dump($result);
    }
}
```

结果

```php

array(3) {
  ["id"]=>
  int(2)
  ["title"]=>
  string(70) "采育公交快线3月30日起开通 将打造京沪高速进城快线"
  ["content"]=>
  string(192) "中途路由：由采育公交场站发出，经南北大街、育林街、福源路、采林路、育圣街、采和路、育英街、采伟路、采林路、京沪高速至十里河桥南。"
}
```

**中文搜索开始测试**

查找title字段中有 公交 一词的文章

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->match('title', '公交')->select();
        var_dump($result);
    }
}
```

结果

```php

array(2) {
  [0]=>
  array(3) {
    ["id"]=>
    int(2)
    ["title"]=>
    string(70) "采育公交快线3月30日起开通 将打造京沪高速进城快线"
    ["content"]=>
    string(192) "中途路由：由采育公交场站发出，经南北大街、育林街、福源路、采林路、育圣街、采和路、育英街、采伟路、采林路、京沪高速至十里河桥南。"
  }
  [1]=>
  array(3) {
    ["id"]=>
    int(3)
    ["title"]=>
    string(39) "公交集团周末拟开通扫墓专线"
    ["content"]=>
    string(696) "宜昌公交集团表示，为方便市民扫墓、踏青，启动了节假日客流高峰运输预案，拟于本周末开通长江市场至夷陵区长陵园公墓、金东山小商品城至窑湾殡仪馆东山公墓两条扫墓专线。同时，对途经几个公墓的公交线路予以密切关注，根据客流情况及时增加班次，“根据近半个月的运营情况，工作日客流出行时间主要集中在上午7:00－9:30，周末客流主要集中每天上午7:00—12:00，线路日均客流较往常增加2000人次左右。除正常增加班次外，公交集团还根据客流出行特点灵活调度，确保线路正常运营，为市民扫墓出行提供保障。”"
  }
}
```

搜索多个词的用逗号分隔

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->match('title', '公交,专线')->select();
        var_dump($result);
    }
}
```

搜索title里有 公交、专线2个词，content里有 市民 的文章内容

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->match('title', '公交,专线')
            ->match('content', '市民')
            ->select();
        var_dump($result);
    }
}
```

结果

```php
array(1) {
  [0]=>
  array(3) {
    ["id"]=>
    int(3)
    ["title"]=>
    string(39) "公交集团周末拟开通扫墓专线"
    ["content"]=>
    string(696) "宜昌公交集团表示，为方便市民扫墓、踏青，启动了节假日客流高峰运输预案，拟于本周末开通长江市场至夷陵区长陵园公墓、金东山小商品城至窑湾殡仪馆东山公墓两条扫墓专线。同时，对途经几个公墓的公交线路予以密切关注，根据客流情况及时增加班次，“根据近半个月的运营情况，工作日客流出行时间主要集中在上午7:00－9:30，周末客流主要集中每天上午7:00—12:00，线路日均客流较往常增加2000人次左右。除正常增加班次外，公交集团还根据客流出行特点灵活调度，确保线路正常运营，为市民扫墓出行提供保障。”"
  }
}
```

删除一条数据，数据中必须要有id（主键）字段

```php
<?php

namespace application\home\controller;


use library\mysmarty\ElasticSearch;

class Index
{
    public function test()
    {
        $result = ElasticSearch::getInstance()->where('id', 3)->del();
        var_dump($result);
    }
}
```

结果

```php
bool(true)
```

目前ElasticSearch类库仅实现了部分功能，你可以使用 `get`，`put`实现自己的查询功能

