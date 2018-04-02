/*
Navicat MySQL Data Transfer

Source Server         : admin.heil.red
Source Server Version : 50721
Source Host           : admin.heil.red:3306
Source Database       : admin_heil_red

Target Server Type    : MYSQL
Target Server Version : 50721
File Encoding         : 65001

Date: 2018-04-02 13:53:12
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for admin_access
-- ----------------------------
DROP TABLE IF EXISTS `admin_access`;
CREATE TABLE `admin_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限id',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '权限标题',
  `uris` varchar(1000) NOT NULL DEFAULT '' COMMENT '权限路径',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '该记录是否有效1：有效、0：无效',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统后台权限表';

-- ----------------------------
-- Table structure for admin_login_log
-- ----------------------------
DROP TABLE IF EXISTS `admin_login_log`;
CREATE TABLE `admin_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT '0' COMMENT '登录类型',
  `state` int(2) DEFAULT '0' COMMENT '登录状态 0成功 1失败 2系统错误级别',
  `info` varchar(11845) DEFAULT '' COMMENT '详细信息',
  `ip` varchar(16) DEFAULT '' COMMENT '登录ip',
  `machine` varchar(555) DEFAULT '' COMMENT '登录的设备',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=1142 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员登录日志表';

-- ----------------------------
-- Table structure for admin_login_main_config
-- ----------------------------
DROP TABLE IF EXISTS `admin_login_main_config`;
CREATE TABLE `admin_login_main_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT '0' COMMENT '登录类型',
  `login_count` int(2) DEFAULT '3' COMMENT '同时登录数量',
  `overdue` int(11) DEFAULT '7200' COMMENT '过期时间 0 不过期 秒',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员登录配置表';

-- ----------------------------
-- Table structure for admin_login_main_token
-- ----------------------------
DROP TABLE IF EXISTS `admin_login_main_token`;
CREATE TABLE `admin_login_main_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT '0' COMMENT '登录类型',
  `login_access_token` varchar(255) DEFAULT '' COMMENT '登录token',
  `login_access_token_salt` varchar(255) DEFAULT '' COMMENT '登录token的salt',
  `login_access_token_time` datetime DEFAULT NULL COMMENT '登录token创建时间',
  `login_info` varchar(255) DEFAULT '' COMMENT '登录的设备或者ip',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_access_token` (`login_access_token`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=241 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员登录记录表';

-- ----------------------------
-- Table structure for admin_main_user
-- ----------------------------
DROP TABLE IF EXISTS `admin_main_user`;
CREATE TABLE `admin_main_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '昵称字符串',
  `login_name` varchar(255) DEFAULT '' COMMENT '登录名称',
  `phone` varchar(11) DEFAULT '' COMMENT '手机号码',
  `email` varchar(255) DEFAULT '' COMMENT '电子邮件',
  `inviter_id` int(11) DEFAULT '0' COMMENT '邀请人',
  `pwd_salt` varchar(255) DEFAULT '' COMMENT '密码盐',
  `pwd_hash` varchar(255) DEFAULT '' COMMENT '密码盐+密码的hash',
  `login_error_count` tinyint(1) DEFAULT '0' COMMENT '登录错误 5次  禁止2小时  发送邮件通知',
  `login_error_count_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '上次密码错误时间',
  `combo` tinyint(1) DEFAULT '0' COMMENT '套餐',
  `grade` tinyint(1) DEFAULT '0' COMMENT '会员等级 1、2、3、4、5、6、7、8、9',
  `user_group` tinyint(1) DEFAULT '0' COMMENT '用户组',
  `balance` decimal(10,0) DEFAULT '0' COMMENT '用户余额',
  `integral` decimal(10,0) DEFAULT '0' COMMENT '用户积分余额',
  `wc_openid` varchar(255) DEFAULT '' COMMENT '微信openid',
  `apy_openid` varchar(255) DEFAULT '' COMMENT '支付宝openid',
  `login_access_token` varchar(255) DEFAULT '' COMMENT '登录token',
  `login_access_token_salt` varchar(255) DEFAULT '' COMMENT '登录token的salt',
  `login_access_token_time` datetime DEFAULT NULL COMMENT '登录token创建时间',
  `autonym` tinyint(1) DEFAULT '0' COMMENT '认证状态，0为正常，1为锁定',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `register_way` varchar(255) DEFAULT '' COMMENT '注册方式',
  `create_time` datetime DEFAULT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_access_token_time` (`login_access_token_time`),
  UNIQUE KEY `login_name` (`login_name`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `wc_openid` (`wc_openid`),
  UNIQUE KEY `apy_openid` (`apy_openid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='核心管理员信息表';

-- ----------------------------
-- Table structure for admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `admin_menu`;
CREATE TABLE `admin_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '视图名称',
  `title` varchar(255) DEFAULT '' COMMENT '显示标题',
  `father_id` varchar(4) DEFAULT '0' COMMENT '父id',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `sort` int(2) unsigned DEFAULT '0' COMMENT '排序',
  `group_id` int(11) DEFAULT '0' COMMENT '权限组',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='后台菜单表';

-- ----------------------------
-- Table structure for admin_menu_access
-- ----------------------------
DROP TABLE IF EXISTS `admin_menu_access`;
CREATE TABLE `admin_menu_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '权限id',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '权限标题',
  `menu_id` varchar(1000) NOT NULL DEFAULT '' COMMENT '菜单id',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '该记录是否有效1：有效、0：无效',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统后台菜单权限表';

-- ----------------------------
-- Table structure for admin_role
-- ----------------------------
DROP TABLE IF EXISTS `admin_role`;
CREATE TABLE `admin_role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '角色id',
  `name` varchar(64) NOT NULL DEFAULT '' COMMENT '角色名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '该记录是否有效1：有效、0：无效',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统管理员角色表';

-- ----------------------------
-- Table structure for admin_role_access
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_access`;
CREATE TABLE `admin_role_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色id',
  `access_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限id',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='角色表与权限表的关联表';

-- ----------------------------
-- Table structure for admin_role_menu_access
-- ----------------------------
DROP TABLE IF EXISTS `admin_role_menu_access`;
CREATE TABLE `admin_role_menu_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色id',
  `access_id` int(11) NOT NULL DEFAULT '0' COMMENT '权限id',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='菜单角色表与权限表的关联表';

-- ----------------------------
-- Table structure for admin_user_role
-- ----------------------------
DROP TABLE IF EXISTS `admin_user_role`;
CREATE TABLE `admin_user_role` (
  `id` int(11) NOT NULL COMMENT '主键',
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '用户id',
  `role_id` int(11) NOT NULL DEFAULT '0' COMMENT '角色id',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='系统管理员与角色表关联表';

-- ----------------------------
-- Table structure for app_login_log
-- ----------------------------
DROP TABLE IF EXISTS `app_login_log`;
CREATE TABLE `app_login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT '0' COMMENT '登录类型',
  `state` int(2) DEFAULT '0' COMMENT '登录状态 0成功 1失败 2系统错误级别',
  `info` varchar(11845) DEFAULT '' COMMENT '详细信息',
  `ip` varchar(16) DEFAULT '' COMMENT '登录ip',
  `machine` varchar(555) DEFAULT '' COMMENT '登录的设备',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户登录日志表';

-- ----------------------------
-- Table structure for app_login_main_config
-- ----------------------------
DROP TABLE IF EXISTS `app_login_main_config`;
CREATE TABLE `app_login_main_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT '0' COMMENT '登录类型',
  `login_count` int(2) DEFAULT '3' COMMENT '同时登录数量',
  `overdue` int(11) DEFAULT '7200' COMMENT '过期时间 0 不过期 秒',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户登录配置表';

-- ----------------------------
-- Table structure for app_login_main_token
-- ----------------------------
DROP TABLE IF EXISTS `app_login_main_token`;
CREATE TABLE `app_login_main_token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户id',
  `type` int(2) DEFAULT '0' COMMENT '登录类型',
  `login_access_token` varchar(255) DEFAULT '' COMMENT '登录token',
  `login_access_token_salt` varchar(255) DEFAULT '' COMMENT '登录token的salt',
  `login_access_token_time` datetime DEFAULT NULL COMMENT '登录token创建时间',
  `login_info` varchar(255) DEFAULT '' COMMENT '登录的设备或者ip',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_access_token` (`login_access_token`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=212 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户登录记录表';

-- ----------------------------
-- Table structure for app_main_user
-- ----------------------------
DROP TABLE IF EXISTS `app_main_user`;
CREATE TABLE `app_main_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) DEFAULT '' COMMENT '昵称字符串',
  `login_name` varchar(255) DEFAULT '' COMMENT '登录名称',
  `phone` varchar(11) DEFAULT '' COMMENT '手机号码',
  `email` varchar(255) DEFAULT '' COMMENT '电子邮件',
  `inviter_id` int(11) DEFAULT '0' COMMENT '邀请人',
  `pwd_salt` varchar(255) DEFAULT '' COMMENT '密码盐',
  `pwd_hash` varchar(255) DEFAULT '' COMMENT '密码盐+密码的hash',
  `login_error_count` tinyint(1) DEFAULT '0' COMMENT '登录错误 5次  禁止2小时  发送邮件通知',
  `login_error_count_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '上次密码错误时间',
  `combo` tinyint(1) DEFAULT '0' COMMENT '套餐',
  `grade` tinyint(1) DEFAULT '0' COMMENT '会员等级 1、2、3、4、5、6、7、8、9',
  `user_group` tinyint(1) DEFAULT '0' COMMENT '用户组',
  `balance` decimal(10,0) DEFAULT '0' COMMENT '用户余额',
  `integral` decimal(10,0) DEFAULT '0' COMMENT '用户积分余额',
  `wc_openid` varchar(255) DEFAULT '' COMMENT '微信openid',
  `apy_openid` varchar(255) DEFAULT '' COMMENT '支付宝openid',
  `autonym` tinyint(1) DEFAULT '0' COMMENT '认证状态，0为正常，1为锁定',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `register_way` varchar(255) DEFAULT '' COMMENT '注册方式',
  `create_time` datetime DEFAULT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login_name` (`login_name`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `wc_openid` (`wc_openid`),
  UNIQUE KEY `apy_openid` (`apy_openid`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='核心用户信息表';

-- ----------------------------
-- Table structure for app_main_user_combo
-- ----------------------------
DROP TABLE IF EXISTS `app_main_user_combo`;
CREATE TABLE `app_main_user_combo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '套餐名称',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `type` varchar(255) DEFAULT '' COMMENT '类型',
  `operator` bigint(20) DEFAULT '0' COMMENT '操作id',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='套餐表';

-- ----------------------------
-- Table structure for app_menu
-- ----------------------------
DROP TABLE IF EXISTS `app_menu`;
CREATE TABLE `app_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '视图名称',
  `title` varchar(255) DEFAULT '' COMMENT '显示标题',
  `father_id` varchar(4) DEFAULT '0' COMMENT '父id',
  `icon` varchar(255) DEFAULT '' COMMENT '图标',
  `sort` int(2) unsigned DEFAULT '0' COMMENT '排序',
  `group_id` int(11) DEFAULT '0' COMMENT '权限组',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='用户菜单表';

-- ----------------------------
-- Table structure for email_log
-- ----------------------------
DROP TABLE IF EXISTS `email_log`;
CREATE TABLE `email_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Type` int(3) DEFAULT '0' COMMENT '发送类型 1、注册验证2、找回密码3、修改密码',
  `sender` varchar(40) DEFAULT '' COMMENT '寄件人昵称',
  `title` varchar(40) DEFAULT '0' COMMENT '主题',
  `receive_email` varchar(255) DEFAULT NULL COMMENT '接收地址',
  `send_email` varchar(255) DEFAULT NULL COMMENT '发送地址',
  `state` int(2) DEFAULT '0' COMMENT '状态 0成功 1失败 ',
  `info` text COMMENT '详细信息',
  `er` varchar(255) DEFAULT '0' COMMENT '错误信息 没有错误0',
  `ip` varchar(16) DEFAULT '' COMMENT '触发ip',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `send_create_time` datetime DEFAULT NULL COMMENT '发送创建时间',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `receive_email` (`receive_email`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='邮件系统日志表';

-- ----------------------------
-- Table structure for error_log
-- ----------------------------
DROP TABLE IF EXISTS `error_log`;
CREATE TABLE `error_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_type` int(3) DEFAULT '0' COMMENT '错误类型0 系统错误 1 管理后台错误  2用户后台 3首页',
  `title` varchar(40) DEFAULT '0' COMMENT '简单标题',
  `fun` varchar(255) DEFAULT NULL COMMENT '请求的模块、控制器、方法',
  `state` int(2) DEFAULT '0' COMMENT '状态 0成功 1失败 ',
  `info` text COMMENT '详细信息',
  `ip` varchar(16) DEFAULT '' COMMENT '登录ip',
  `machine` varchar(555) DEFAULT '' COMMENT '客户端信息',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `ip` (`ip`),
  KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=4808 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='系统错误日志表';

-- ----------------------------
-- Table structure for region
-- ----------------------------
DROP TABLE IF EXISTS `region`;
CREATE TABLE `region` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `level` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `upid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `upid` (`upid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=45052 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for ssr_user_log
-- ----------------------------
DROP TABLE IF EXISTS `ssr_user_log`;
CREATE TABLE `ssr_user_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL COMMENT '管理员id',
  `uid` int(11) DEFAULT NULL COMMENT 'SSR用户id',
  `Type` int(2) DEFAULT '0' COMMENT '操作类型',
  `state` int(2) DEFAULT '0' COMMENT '登录状态 0成功 1失败 2系统错误级别',
  `info` varchar(255) DEFAULT '' COMMENT '详细信息',
  `ip` varchar(16) DEFAULT '' COMMENT '操作ip',
  `status` tinyint(1) DEFAULT '0' COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) DEFAULT '0' COMMENT '软删除  0正常  1删除',
  `create_time` datetime DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='SSR用户日志表';
