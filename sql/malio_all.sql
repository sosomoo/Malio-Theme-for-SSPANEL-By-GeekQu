/* 给 user 表增加 uuid, phone 字段 */
ALTER TABLE `user` ADD `uuid` text;
ALTER TABLE `user` ADD `phone` bigint(20) AFTER `email`;

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
