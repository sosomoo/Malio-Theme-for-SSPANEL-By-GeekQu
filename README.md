# 请勿泄露源码给他人
## 安装
在网站目录下 
`git clone -b master https://gitlab.com/maxitio/malio-theme-for-sspanel.git tmp && mv tmp/.git . && rm -rf tmp && git reset --hard`

根据sspanel的wiki安装后，将config目录下的.malio_config.example.php 复制一份命名为 .malio_config.php

修改.config.php里的默认主题为malio

将数据库user表里的全部用户的theme列改为malio，可以使用这条SQL语句👉 `UPDATE user SET theme='malio'`

客户端的安装包需要自行下载到 `/public/client-download/` 目录，安装包名字参考 `/resources/views/malio/user/tutorial` 文件夹下的tpl文件内的名字

部署好了之后就可以找我拿js授权文件

## 注意事项
.malio_config.php 文件里的商品id必须设置好，不然没办法购买。

在.config.php里设置新用户注册等级为-1。

在.config.php里设置用户等级过期时间，建议设置为超过一个月的时间(720小时)。

## TG群
[https://t.me/joinchat/DM2_FxStXAbYZ2DzVfZjcw](https://t.me/joinchat/DM2_FxStXAbYZ2DzVfZjcw)

## CREDIT
基于 [rico](https://github.com/rico93) 和 [GeekQu](https://github.com/GeekQu) 维护的 [ss-panel-v3-mod_Uim](https://github.com/rico93/ss-panel-v3-mod_Uim) 修改