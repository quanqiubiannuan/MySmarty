**在指定的文件夹（/root/myssl）内生成证书**

`cd`

`mkdir myssl`

`cd myssl/`

**创建私钥**（test.key）

test.key为名称，123456为密码

```nginx
[root@localhost myssl]# openssl genrsa -des3 -out test.key 1024
Generating RSA private key, 1024 bit long modulus
............................................................++++++
............................................++++++
e is 65537 (0x10001)
Enter pass phrase for test.key:123456
Verifying - Enter pass phrase for test.key:123456
```

输入密码后，再次重复输入确认密码，记住此密码，后面会用到

```nginx
[root@localhost myssl]# ll
总用量 4
-rw-r--r-- 1 root root 951 4月  20 19:42 test.key
```

**创建csr证书**（test.csr）

test.key为上面创建的key，由于是测试的证书，信息可以随便填

```nginx
[root@localhost myssl]# openssl req -new -key test.key -out test.csr
Enter pass phrase for test.key:
You are about to be asked to enter information that will be incorporated
into your certificate request.
What you are about to enter is what is called a Distinguished Name or a DN.
There are quite a few fields but you can leave some blank
For some fields there will be a default value,
If you enter '.', the field will be left blank.
-----
Country Name (2 letter code) [XX]:cn
State or Province Name (full name) []:hb
Locality Name (eg, city) [Default City]:wh
Organization Name (eg, company) [Default Company Ltd]:gg
Organizational Unit Name (eg, section) []:gg
Common Name (eg, your name or your server's hostname) []:dj
Email Address []:122@qq.com

Please enter the following 'extra' attributes
to be sent with your certificate request
A challenge password []:123456
An optional company name []:gg
```

```nginx
[root@localhost myssl]# ll
总用量 8
-rw-r--r-- 1 root root 708 4月  20 19:47 test.csr
-rw-r--r-- 1 root root 951 4月  20 19:42 test.key
```

**去除密码**

在加载SSL支持的Nginx并使用上述私钥时除去必须的口令，否则会在启动nginx的时候需要输入密码

备份秘钥（test.key）

```nginx
[root@localhost myssl]# cp test.key test.key.bak
```

```nginx
[root@localhost myssl]# ll
总用量 12
-rw-r--r-- 1 root root 708 4月  20 19:47 test.csr
-rw-r--r-- 1 root root 951 4月  20 19:42 test.key
-rw-r--r-- 1 root root 951 4月  20 19:50 test.key.bak
```

生成没秘钥的test_nopassword.key

123456为创建test.key的密码

```nginx
[root@localhost myssl]# openssl rsa -in test.key -out test_nopassword.key
Enter pass phrase for test.key:123456
writing RSA key
```

```nginx
[root@localhost myssl]# ll
总用量 16
-rw-r--r-- 1 root root 708 4月  20 19:47 test.csr
-rw-r--r-- 1 root root 951 4月  20 19:42 test.key
-rw-r--r-- 1 root root 951 4月  20 19:50 test.key.bak
-rw-r--r-- 1 root root 887 4月  20 19:51 test_nopassword.key
```

**生成crt证书**（test.crt）

```nginx
[root@localhost myssl]# openssl x509 -req -days 3650 -in test.csr -signkey test_nopassword.key -out test.crt
Signature ok
subject=/C=cn/ST=hb/L=wh/O=gg/OU=gg/CN=dj/emailAddress=122@qq.com
Getting Private key
```

```nginx
[root@localhost myssl]# ll
总用量 20
-rw-r--r-- 1 root root 855 4月  20 19:54 test.crt
-rw-r--r-- 1 root root 708 4月  20 19:47 test.csr
-rw-r--r-- 1 root root 951 4月  20 19:42 test.key
-rw-r--r-- 1 root root 951 4月  20 19:50 test.key.bak
-rw-r--r-- 1 root root 887 4月  20 19:51 test_nopassword.key
```

**配置nginx**

在nginx配置目录下创建ssl文件夹，用来存放证书

```nginx
[root@localhost ~]# cd /etc/nginx/
```

```nginx
[root@localhost nginx]# mkdir ssl
```

复制证书到ssl文件夹（/etc/nginx/ssl/）

```nginx
[root@localhost myssl]# cp test.crt /etc/nginx/ssl/
[root@localhost myssl]# cp test_nopassword.key /etc/nginx/ssl/
```

```nginx
server {
    listen              443 ssl;
    server_name         www.example.com;
    ssl_certificate     ssl/test.crt;
    ssl_certificate_key ssl/test_nopassword.key;
    ssl_protocols       TLSv1 TLSv1.1 TLSv1.2;
    ssl_ciphers         HIGH:!aNULL:!MD5;
    ...
}
```

重启nginx

`service nginx restart`

