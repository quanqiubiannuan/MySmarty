官网： http://sphinxsearch.com/ 

**Windows 使用**

1，下载软件

 http://sphinxsearch.com/downloads/current/ 

http://sphinxsearch.com/downloads/sphinx-3.1.1-612d99f-windows-amd64.zip

无需安装，解压即可使用

2，配置索引文件(spinx.conf)

```
source daan
{
	type			= mysql

	sql_host		= 127.0.0.1
	sql_user		= root
	sql_pass		= 123456
	sql_db			= wyzda
	sql_port		= 3306	# optional, default is 3306
	sql_query_pre		= SET NAMES utf8

	sql_query		= \
		SELECT id, content \
		FROM daan

	sql_field_string = content
}


index daan
{
	ngram_len		= 1
	ngram_chars		= U+3000..U+2FA1F
	html_strip		= 1
	source			= daan
	path			= E:/sphinx-3.1.1/data/daan
}	

indexer
{
	mem_limit		= 128M
}

searchd
{
	listen			= 9312
	listen			= 9306:mysql41
	log				= E:/sphinx-3.1.1/data/searchd.log
	query_log		= E:/sphinx-3.1.1/data/query.log
	read_timeout		= 5
	max_children		= 30
	pid_file		= E:/sphinx-3.1.1/data/searchd.pid
	seamless_rotate		= 1
	preopen_indexes		= 1
	unlink_old		= 1
	workers			= threads # for RT to work
	binlog_path		= E:/sphinx-3.1.1/data/data
	collation_server		= utf8_general_ci
}
```

配置中的相关目录必须存在，数据库的配置必须正确

3，索引数据

进入到indexer.exe所在文件夹

`indexer.exe --config E:\sphinx-3.1.1\data\spinx.conf --all`

```
E:\sphinx-3.1.1\bin>indexer.exe --config E:\sphinx-3.1.1\data\spinx.conf --all
Sphinx 3.1.1-dev (commit 612d99f)
Copyright (c) 2001-2018, Andrew Aksyonoff
Copyright (c) 2008-2016, Sphinx Technologies Inc (http://sphinxsearch.com)

using config file 'E:\sphinx-3.1.1\data\spinx.conf'...
indexing index 'daan'...
^Cllected 887000 docs, 832.2 MB
```

4，启动搜索

进入到searchd.exe目录

`searchd.exe --config E:\sphinx-3.1.1\data\spinx.conf`

```
E:\sphinx-3.1.1\bin>searchd.exe --config E:\sphinx-3.1.1\data\spinx.conf
```

5，cmd窗口客户端连接

mysql是数据库的命令行文件，确保可以在任何地方都可以执行mysql命令

`mysql -h127.0.0.1 -P9306`

`E:\sphinx-3.1.1\bin>mysql -h127.0.0.1 -P9306`

6，代码使用

先配置SphinxSearch

`\config\database.php`

```php
'sphinxsearch' => [
	// 主机ip
	'host' => '127.0.0.1',
	// mysql 端口
	'port' => 9306,
	// mysql 字符编码
	'charset' => 'utf8mb4'
]
```

sql语句中要匹配的汉字用单引号，不要用双引号！

```php
$res = SphinxSearch::getInstance()->query("select id from daan where match('李白') limit 5");
var_dump($res);
```

**Centos**

您也可以按照Windows的安装方式使用最新版

下面是使用包安装的方式

 http://sphinxsearch.com/downloads/archive/ 

1，安装必要的软件

`yum install postgresql-libs unixODBC`

2，下载最新的rpm包

`wget http://sphinxsearch.com/files/sphinx-2.3.2-1.rhel5.x86_64.rpm`

3，安装包

`rpm -Uhv sphinx-2.3.2-1.rhel7.x86_64.rpm`

4，编辑索引文件，参考windows配置

`vim /etc/sphinx/sphinx.conf`

5，索引数据

`indexer --all --rotate`

6，启动搜索

`searchd`

7，配置开机启动

`systemctl enable searchd.service`

具体使用方法参考windows