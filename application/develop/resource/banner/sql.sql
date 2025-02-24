CREATE TABLE IF NOT EXISTS `__DB_PRE__banner` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `position` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '位置 1-首页banner',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '类型 0-不跳转 1-小程序页面 2-外链',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '名称',
  `image` varchar(128) NOT NULL DEFAULT '' COMMENT '图片',
  `url` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转地址',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1正常 0禁用',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `position` (`position`),
  KEY `status` (`status`),
  KEY `sort` (`sort`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='banner表';