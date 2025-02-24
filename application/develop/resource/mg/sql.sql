CREATE TABLE IF NOT EXISTS `__DB_PRE__mg_member` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `mg_module` varchar(32) NOT NULL DEFAULT '' COMMENT '管理端',
  `group_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '管理组ID',
  `nickname` varchar(64) NOT NULL DEFAULT '' COMMENT '昵称',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(32) NOT NULL DEFAULT '' COMMENT '密码',
  `salt` varchar(6) NOT NULL DEFAULT '' COMMENT '加密字符串',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1正常 0禁用',
  `create_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '修改时间',
  `last_login_time` int(11) unsigned NOT NULL DEFAULT 0 COMMENT '上次登陆时间',
  `last_login_ip` varchar(15) NOT NULL DEFAULT '' COMMENT '上次登录IP',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY (`mg_module`)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='管理员表';

CREATE TABLE IF NOT EXISTS `__DB_PRE__mg_group` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `mg_module` varchar(32) NOT NULL DEFAULT '' COMMENT '管理端',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级ID',
  `group_name` varchar(32) NOT NULL COMMENT '权限组名称',
  `access` text COMMENT '权限ID',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1正常 0禁用',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY (`mg_module`)
) ENGINE=innodb DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='管理组表';

CREATE TABLE IF NOT EXISTS `__DB_PRE__mg_menu` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键',
  `mg_module` varchar(32) NOT NULL DEFAULT '' COMMENT '管理端',
  `parent_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级菜单ID',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '权限菜单名称',
  `router` varchar(128) NOT NULL DEFAULT '' COMMENT '路由地址',
  `module` varchar(32) NOT NULL DEFAULT '' COMMENT '模块名',
  `controller` varchar(32) NOT NULL DEFAULT '' COMMENT '控制器名',
  `action` varchar(32) NOT NULL DEFAULT '' COMMENT '方法名',
  `icon` varchar(32) NOT NULL DEFAULT '' COMMENT 'icon图标',
  `style` varchar(32) NOT NULL DEFAULT '' COMMENT '按钮样式',
  `sort` tinyint(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1正常 0禁用',
  `create_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY (`mg_module`, `sort`, `status`),
  KEY `parent_id` (`parent_id`, `status`)
) ENGINE=innodb DEFAULT CHARSET=utf8 COMMENT='权限菜单表';