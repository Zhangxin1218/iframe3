CREATE TABLE IF NOT EXISTS `__DB_PRE__card` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '奖品类型 1-普通卡 2-稀有卡',
  `name` varchar(86) NOT NULL DEFAULT '' COMMENT '卡片名称',
  `image` varchar(128) NOT NULL DEFAULT '' COMMENT '卡片图片',
  `stock` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总库存',
  `day_stock` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '日发放数量',
  `win_times` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '中奖次数 1-只中1次 0-不限次数',
  `ratio` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '中奖概率',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1-上架 0-下架',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='卡片表';

CREATE TABLE IF NOT EXISTS `__DB_PRE__card_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
  `card_id` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '卡片ID',
  `card_type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '奖品类型 1-普通卡 2-稀有卡',
  `card_name` varchar(86) NOT NULL DEFAULT '' COMMENT '卡片名称',
  `card_image` varchar(128) NOT NULL DEFAULT '' COMMENT '卡片图片',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态 1-已使用 0-未使用',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '获得时间',
  `log_date` varchar(16) NOT NULL DEFAULT '' COMMENT '记录日期',
  `use_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '使用时间',
  PRIMARY KEY (`id`),
  KEY `card_type` (`card_type`),
  KEY (`user_id`, `card_id`, `status`),
  KEY (`card_id`, `log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户卡片表';

CREATE TABLE `__DB_PRE__card_share` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `log_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '中奖时间',
  `log_date` varchar(16) NOT NULL DEFAULT '' COMMENT '中奖日期',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`,`log_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='集卡分享表';