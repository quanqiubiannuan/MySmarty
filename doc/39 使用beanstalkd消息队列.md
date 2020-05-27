**介绍**

**Beanstalk，一个高性能、轻量级的分布式内存队列系统，最初设计的目的是想通过后台异步执行耗时的任务来降低高容量Web应用系统的页面访问延迟，支持过有9.5 million用户的Facebook Causes应用。**

**一、Beanstalkd是什么？**

Beanstalkd是一个高性能，轻量级的分布式内存队列

**二、Beanstalkd特性**

1、支持优先级(支持任务插队)
2、延迟(实现定时任务)
3、持久化(定时把内存中的数据刷到binlog日志)
4、预留(把任务设置成预留，消费者无法取出任务，等某个合适时机再拿出来处理)
5、任务超时重发(消费者必须在指定时间内处理任务，如果没有则认为任务失败，重新进入队列)

**三、Beanstalkd核心元素**

生产者 -> 管道(tube) -> 任务(job) -> 消费者

Beanstalkd可以创建多个管道，管道里面存了很多任务，消费者从管道中取出任务进行处理。

**四、任务job状态**

delayed 延迟状态
ready 准备好状态
reserved 消费者把任务读出来，处理时
buried 预留状态
delete 删除状态

**安装**

**Linux centos上安装**

git 下载

```
git clone https://github.com/beanstalkd/beanstalkd.git
```

安装

```
cd beanstalkd
make
```

运行

```
./beanstalkd
```

or

```
./beanstalkd -l 10.0.1.5 -p 11300
```

-l 指定ip
-p 指定端口

**其它安装方式**

Debian

```
sudo apt-get install beanstalkd
```

Ubuntu

```
sudo apt-get install beanstalkd
```

OS X

```
brew install beanstalkd
```

MacPorts

```
sudo port install beanstalkd
```

Fedora 9 and Fedora 10

```
su -c 'yum install beanstalkd'
```

CentOS and RHEL

```
su -c 'rpm -Uvh http://download.fedora.redhat.com/pub/epel/5/i386/epel-release-5-3.noarch.rpm'
su -c 'yum install beanstalkd --enablerepo=epel-testing'
```

Arch Linux

安装 yaourt，然后

```
yaourt -S beanstalkd
```

Gentoo

```
sudo emerge beanstalkd
```

**开始使用**

生产者示例

```php
<?php

namespace application\home\controller;

use library\mysmarty\Beanstalkd;
use library\mysmarty\Controller;

class Index extends Controller
{

    public function test()
    {
        var_dump(Beanstalkd::getInstance()->put('aaaaa'));
    }
}
```

```php
string(2) "91"
```

消费者示例

```php
<?php

namespace application\home\controller;

use library\mysmarty\Beanstalkd;
use library\mysmarty\Controller;

class Index extends Controller
{
    public function test2()
    {
        while (true){
            Beanstalkd::getInstance()->watch('default');
            $data = Beanstalkd::getInstance()->reserve();
            var_dump($data);
        }
    }
}
```

获取到数据后请及时消费掉，删除掉！

消费者需要在后台长期运行，所以请在控制台执行！参考 16 使用命令行

**其它方法请参考代码提示！**