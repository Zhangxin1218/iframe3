CREATE TABLE IF NOT EXISTS `__DB_PRE__prize` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '奖品类型',
  `name` varchar(128) NOT NULL DEFAULT '' COMMENT '奖品名称',
  `image` varchar(128) NOT NULL DEFAULT '' COMMENT '奖品图片',
  `value` varchar(128) NOT NULL DEFAULT '' COMMENT '奖品附加信息',
  `stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '库存',
  `day_stock` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '每日库存',
  `win_times` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否只能中一次 1-是 0-否',
  `ratio` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '中奖概率',
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排序',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1-上架 0-下架',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`),
  KEY `sort` (`sort`),
  KEY `ratio` (`ratio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='奖品表';

CREATE TABLE IF NOT EXISTS `__DB_PRE__prize_code` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `prize_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '奖品ID',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '卡券编码',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1-已发放 0-未发放',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `send_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '发放时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `prize_id` (`prize_id`,`status`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='虚拟卡券券码表';

CREATE TABLE IF NOT EXISTS `__DB_PRE__prize_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `prize_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '奖品ID',
  `prize_type` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '奖品类型',
  `prize_name` varchar(128) NOT NULL DEFAULT '' COMMENT '奖品名称',
  `prize_image` varchar(128) NOT NULL DEFAULT '' COMMENT '奖品图片',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 0-未领取 1-已领取',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '虚拟卡券编码',
  `value` varchar(128) NOT NULL DEFAULT '' COMMENT '奖品附加信息',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '姓名',
  `mobile` varchar(16) NOT NULL DEFAULT '' COMMENT '电话',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '中奖时间',
  `log_date` varchar(16) NOT NULL DEFAULT '' COMMENT '中奖日期',
  `receive_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '留资时间',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`prize_id`,`log_date`),
  KEY `prize_id` (`prize_id`,`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='奖品中奖记录表';

CREATE TABLE IF NOT EXISTS `__DB_PRE__prize_share` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '中奖时间',
  `log_date` varchar(16) NOT NULL DEFAULT '' COMMENT '中奖日期',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='分享表';