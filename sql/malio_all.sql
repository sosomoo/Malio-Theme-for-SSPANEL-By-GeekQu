/* 给 user 表增加 uuid, phone 字段 */
ALTER TABLE `user` ADD `uuid` text;
ALTER TABLE `user` ADD `phone` bigint(20) AFTER `email`;

/* 给 user 表 email 字段增加唯一属性，防止重复注册 */
ALTER TABLE `user` ADD UNIQUE (`email`);

/* 给 code 表增加 tradeno 字段，防止重复到账 */
ALTER TABLE `code` ADD COLUMN `tradeno` varchar(255) NULL;
ALTER TABLE `code` ADD UNIQUE (`tradeno`);

/* 增加 短信验证码 表 */
CREATE TABLE IF NOT EXISTS `sms_verify` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `phone` bigint(20) NOT NULL,
  `code` text NOT NULL,
  `ip` text NOT NULL,
  `expire_in` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;



ALTER TABLE `user` ADD COLUMN `lang` varchar(128) NOT NULL DEFAULT 'zh-cn' COMMENT '用户的语言';
UPDATE `user` SET `lang` = 'zh-cn';
