**centos安装mysql5.7**

更新源

```
wget -i -c http://dev.mysql.com/get/mysql57-community-release-el7-10.noarch.rpm
```

```
yum -y install mysql57-community-release-el7-10.noarch.rpm
```

安装mysql5.7

```
yum -y install mysql-community-server
```

启动mysql

```
systemctl start  mysqld.service
```

查看mysql启动状态

```
systemctl status mysqld.service
```

查看mysql默认密码

```
grep "password" /var/log/mysqld.log
```

登录数据库并修改密码

```
mysql -u root -p
```

输入刚才的默认数据库密码

然后修改密码

```
ALTER USER 'root'@'localhost' IDENTIFIED BY '新密码';
```

注意新密码有规则的，简单密码是不能修改成功

修改密码后重启下数据库就可以了