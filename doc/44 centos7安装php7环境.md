 php包安装网站：https://webtatic.com/ 

首先确认您的系统版本

 CentOS/RHEL 7.x: 

```
yum install epel-release
rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm
```

然后可以执行

```
yum install php72w-fpm php72w-opcache
```

安装php扩展。

下面是可以安装的扩展名

| 扩展名              | Provides                                         |
| ------------------- | :----------------------------------------------- |
| mod_php72w          | php72w, mod_php, php72w-zts                      |
| php72w-bcmath       |                                                  |
| php72w-cli          | php-cgi, php-pcntl, php-readline                 |
| php72w-dba          |                                                  |
| php72w-devel        |                                                  |
| php72w-embedded     | php-embedded-devel                               |
| php72w-enchant      |                                                  |
| php72w-fpm          |                                                  |
| php72w-gd           |                                                  |
| php72w-imap         |                                                  |
| php72w-interbase    | php_database, php-firebird                       |
| php72w-intl         |                                                  |
| php72w-ldap         |                                                  |
| php72w-mbstring     |                                                  |
| php72w-mysql        | php-mysqli, php_database                         |
| php72w-mysqlnd      | php-mysqli, php_database                         |
| php72w-odbc         | php-pdo_odbc, php_database                       |
| php72w-opcache      | php72w-pecl-zendopcache                          |
| php72w-pdo          | php72w-pdo_sqlite, php72w-sqlite3                |
| php72w-pdo_dblib    | php72w-mssql                                     |
| php72w-pear         |                                                  |
| php72w-pecl-apcu    |                                                  |
| php72w-pecl-imagick |                                                  |
| php72w-pecl-mongodb |                                                  |
| php72w-pgsql        | php-pdo_pgsql, php_database                      |
| php72w-phpdbg       |                                                  |
| php72w-process      | php-posix, php-sysvmsg, php-sysvsem, php-sysvshm |
| php72w-pspell       |                                                  |
| php72w-recode       |                                                  |
| php72w-snmp         |                                                  |
| php72w-soap         |                                                  |
| php72w-sodium       |                                                  |
| php72w-tidy         |                                                  |
| php72w-xml          | php-dom, php-domxml, php-wddx, php-xsl           |
| php72w-xmlrpc       |                                                  |

安装完成后，可以执行

`php -v`

> PHP 5.4.16 (cli) (built: Nov  1 2019 16:04:20) 
> Copyright (c) 1997-2013 The PHP Group
> Zend Engine v2.4.0, Copyright (c) 1998-2013 Zend Technologies
> [root@VM_90_104_centos ~]# php -v
> PHP 5.4.16 (cli) (built: Nov  1 2019 16:04:20) 
> Copyright (c) 1997-2013 The PHP Group
> Zend Engine v2.4.0, Copyright (c) 1998-2013 Zend Technologies

出现以上显示，就表示PHP安装成功了。