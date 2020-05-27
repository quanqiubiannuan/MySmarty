nginx官方网站： http://nginx.org/ 

nginx下载地址： http://nginx.org/en/download.html 

Linux操作系统可以参考此页面安装： http://nginx.org/en/linux_packages.html 

一、安装yum-utils

```
yum install yum-utils
```

二、新建文件

 `/etc/yum.repos.d/nginx.repo` 

内容如下

```
[nginx-stable]
name=nginx stable repo
baseurl=http://nginx.org/packages/centos/$releasever/$basearch/
gpgcheck=1
enabled=1
gpgkey=https://nginx.org/keys/nginx_signing.key
module_hotfixes=true
```

三、安装nginx

```
yum install nginx
```

四、启动nginx

`service nginx start`

打开浏览器访问服务器ip，出现nginx页面则代表安装成功。

五、配置php支持

`/etc/nginx/conf.d/default.conf`

打开注释即可，同时注意安装php-fpm，并确认已经启动。

```
location ~ \.php$ {
        root           html;
        fastcgi_pass   127.0.0.1:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  /usr/share/nginx/html$fastcgi_script_name;
        include        fastcgi_params;
}
```

