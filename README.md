# 请勿泄露源码给他人
## 安装
在网站目录下 
`git clone -b master https://gitlab.com/maxitio/malio-theme-for-sspanel.git tmp && mv tmp/.git . && rm -rf tmp && git reset --hard`

根据sspanel的wiki安装后，将config目录下的.malio_config.example.php 复制一份命名为 .malio_config.php

将数据库user表里的全部用户的theme列改为malio，可以使用这条SQL语句👉 `UPDATE user SET theme='malio'`

## 注意事项
.malio_config.php 文件里的商品id必须设置好，不然没办法购买。
在.config.php里设置新用户注册等级为-1。
在.config.php里设置用户等级过期时间，建议设置为超过一个月的时间(720小时)。

## CREDIT
基于 rico 和