/*
 Navicat Premium Data Transfer

 Source Server         : admin_heil_red
 Source Server Type    : MySQL
 Source Server Version : 50721
 Source Host           : admin.heil.red:3306
 Source Schema         : admin_heil_red

 Target Server Type    : MySQL
 Target Server Version : 50721
 File Encoding         : 65001

 Date: 24/03/2018 23:51:09
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for heil_admin_login_log
-- ----------------------------
DROP TABLE IF EXISTS `heil_admin_login_log`;
CREATE TABLE `heil_admin_login_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` int(2) NULL DEFAULT 0 COMMENT '登录类型',
  `state` int(2) NULL DEFAULT 0 COMMENT '登录状态 0成功 1失败 2系统错误级别',
  `info` varchar(11845) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '详细信息',
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录ip',
  `machine` varchar(555) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录的设备',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 910 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员登录日志表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_admin_login_main_config
-- ----------------------------
DROP TABLE IF EXISTS `heil_admin_login_main_config`;
CREATE TABLE `heil_admin_login_main_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` int(2) NULL DEFAULT 0 COMMENT '登录类型',
  `login_count` int(2) NULL DEFAULT 3 COMMENT '同时登录数量',
  `overdue` int(11) NULL DEFAULT 7200 COMMENT '过期时间 0 不过期 秒',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员登录配置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_admin_login_main_token
-- ----------------------------
DROP TABLE IF EXISTS `heil_admin_login_main_token`;
CREATE TABLE `heil_admin_login_main_token`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` int(2) NULL DEFAULT 0 COMMENT '登录类型',
  `login_access_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录token',
  `login_access_token_salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录token的salt',
  `login_access_token_time` datetime(0) NULL DEFAULT NULL COMMENT '登录token创建时间',
  `login_info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录的设备或者ip',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `login_access_token`(`login_access_token`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 204 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员登录记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_admin_main_admin
-- ----------------------------
DROP TABLE IF EXISTS `heil_admin_main_admin`;
CREATE TABLE `heil_admin_main_admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '昵称字符串',
  `login_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录名称',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '电子邮件',
  `inviter_id` int(11) NULL DEFAULT 0 COMMENT '邀请人',
  `pwd_salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '密码盐',
  `pwd_hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '密码盐+密码的hash',
  `login_error_count` tinyint(1) NULL DEFAULT 0 COMMENT '登录错误 5次  禁止2小时  发送邮件通知',
  `login_error_count_time` datetime(0) NULL COMMENT '上次密码错误时间',
  `combo` tinyint(1) NULL DEFAULT 0 COMMENT '套餐',
  `grade` tinyint(1) NULL DEFAULT 0 COMMENT '会员等级 1、2、3、4、5、6、7、8、9',
  `user_group` tinyint(1) NULL DEFAULT 0 COMMENT '用户组',
  `balance` decimal(10, 0) NULL DEFAULT 0 COMMENT '用户余额',
  `integral` decimal(10, 0) NULL DEFAULT 0 COMMENT '用户积分余额',
  `wc_openid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '微信openid',
  `apy_openid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '支付宝openid',
  `login_access_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录token',
  `login_access_token_salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录token的salt',
  `login_access_token_time` datetime(0) NULL DEFAULT NULL COMMENT '登录token创建时间',
  `autonym` tinyint(1) NULL DEFAULT 0 COMMENT '认证状态，0为正常，1为锁定',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `register_way` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '注册方式',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `login_access_token_time`(`login_access_token_time`) USING BTREE,
  UNIQUE INDEX `login_name`(`login_name`) USING BTREE,
  UNIQUE INDEX `phone`(`phone`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE,
  UNIQUE INDEX `wc_openid`(`wc_openid`) USING BTREE,
  UNIQUE INDEX `apy_openid`(`apy_openid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '核心管理员信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `heil_admin_menu`;
CREATE TABLE `heil_admin_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '视图名称',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '显示标题',
  `father_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '父id',
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '图标',
  `sort` int(2) UNSIGNED NULL DEFAULT 0 COMMENT '排序',
  `group_id` int(11) NULL DEFAULT 0 COMMENT '权限组',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 72 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '后台菜单表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_app_login_log
-- ----------------------------
DROP TABLE IF EXISTS `heil_app_login_log`;
CREATE TABLE `heil_app_login_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` int(2) NULL DEFAULT 0 COMMENT '登录类型',
  `state` int(2) NULL DEFAULT 0 COMMENT '登录状态 0成功 1失败 2系统错误级别',
  `info` varchar(11845) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '详细信息',
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录ip',
  `machine` varchar(555) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录的设备',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 195 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户登录日志表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_app_login_main_config
-- ----------------------------
DROP TABLE IF EXISTS `heil_app_login_main_config`;
CREATE TABLE `heil_app_login_main_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` int(2) NULL DEFAULT 0 COMMENT '登录类型',
  `login_count` int(2) NULL DEFAULT 3 COMMENT '同时登录数量',
  `overdue` int(11) NULL DEFAULT 7200 COMMENT '过期时间 0 不过期 秒',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户登录配置表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_app_login_main_token
-- ----------------------------
DROP TABLE IF EXISTS `heil_app_login_main_token`;
CREATE TABLE `heil_app_login_main_token`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `type` int(2) NULL DEFAULT 0 COMMENT '登录类型',
  `login_access_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录token',
  `login_access_token_salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录token的salt',
  `login_access_token_time` datetime(0) NULL DEFAULT NULL COMMENT '登录token创建时间',
  `login_info` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录的设备或者ip',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '登录时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `login_access_token`(`login_access_token`) USING BTREE,
  INDEX `uid`(`uid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 212 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户登录记录表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_app_main_user
-- ----------------------------
DROP TABLE IF EXISTS `heil_app_main_user`;
CREATE TABLE `heil_app_main_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '昵称字符串',
  `login_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录名称',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '手机号码',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '电子邮件',
  `inviter_id` int(11) NULL DEFAULT 0 COMMENT '邀请人',
  `pwd_salt` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '密码盐',
  `pwd_hash` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '密码盐+密码的hash',
  `login_error_count` tinyint(1) NULL DEFAULT 0 COMMENT '登录错误 5次  禁止2小时  发送邮件通知',
  `login_error_count_time` datetime(0) NULL COMMENT '上次密码错误时间',
  `combo` tinyint(1) NULL DEFAULT 0 COMMENT '套餐',
  `grade` tinyint(1) NULL DEFAULT 0 COMMENT '会员等级 1、2、3、4、5、6、7、8、9',
  `user_group` tinyint(1) NULL DEFAULT 0 COMMENT '用户组',
  `balance` decimal(10, 0) NULL DEFAULT 0 COMMENT '用户余额',
  `integral` decimal(10, 0) NULL DEFAULT 0 COMMENT '用户积分余额',
  `wc_openid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '微信openid',
  `apy_openid` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '支付宝openid',
  `autonym` tinyint(1) NULL DEFAULT 0 COMMENT '认证状态，0为正常，1为锁定',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `register_way` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '注册方式',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `login_name`(`login_name`) USING BTREE,
  UNIQUE INDEX `phone`(`phone`) USING BTREE,
  UNIQUE INDEX `email`(`email`) USING BTREE,
  UNIQUE INDEX `wc_openid`(`wc_openid`) USING BTREE,
  UNIQUE INDEX `apy_openid`(`apy_openid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 37 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '核心用户信息表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_app_main_user_combo
-- ----------------------------
DROP TABLE IF EXISTS `heil_app_main_user_combo`;
CREATE TABLE `heil_app_main_user_combo`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '套餐名称',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '备注',
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '类型',
  `operator` bigint(20) NULL DEFAULT 0 COMMENT '操作id',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '套餐表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_app_menu
-- ----------------------------
DROP TABLE IF EXISTS `heil_app_menu`;
CREATE TABLE `heil_app_menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '视图名称',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '显示标题',
  `father_id` varchar(4) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '父id',
  `icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '图标',
  `sort` int(2) UNSIGNED NULL DEFAULT 0 COMMENT '排序',
  `group_id` int(11) NULL DEFAULT 0 COMMENT '权限组',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 60 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '用户菜单表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_email_log
-- ----------------------------
DROP TABLE IF EXISTS `heil_email_log`;
CREATE TABLE `heil_email_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `Type` int(3) NULL DEFAULT 0 COMMENT '发送类型 1、注册验证2、找回密码3、修改密码',
  `sender` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '寄件人昵称',
  `title` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '主题',
  `receive_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '接收地址',
  `send_email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '发送地址',
  `state` int(2) NULL DEFAULT 0 COMMENT '状态 0成功 1失败 ',
  `info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详细信息',
  `er` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '错误信息 没有错误0',
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '触发ip',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `send_create_time` datetime(0) NULL DEFAULT NULL COMMENT '发送创建时间',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `receive_email`(`receive_email`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 62 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '邮件系统日志表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_error_log
-- ----------------------------
DROP TABLE IF EXISTS `heil_error_log`;
CREATE TABLE `heil_error_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_type` int(3) NULL DEFAULT 0 COMMENT '错误类型0 系统错误 1 管理后台错误  2用户后台 3首页',
  `title` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0' COMMENT '简单标题',
  `fun` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '请求的模块、控制器、方法',
  `state` int(2) NULL DEFAULT 0 COMMENT '状态 0成功 1失败 ',
  `info` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '详细信息',
  `ip` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '登录ip',
  `machine` varchar(555) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '客户端信息',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '状态，0为正常，1为锁定',
  `isdel` tinyint(1) NULL DEFAULT 0 COMMENT '软删除  0正常  1删除',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ip`(`ip`) USING BTREE,
  INDEX `title`(`title`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3839 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '系统错误日志表' ROW_FORMAT = Compact;

-- ----------------------------
-- Table structure for heil_region
-- ----------------------------
DROP TABLE IF EXISTS `heil_region`;
CREATE TABLE `heil_region`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `level` tinyint(4) UNSIGNED NOT NULL DEFAULT 0,
  `upid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `upid`(`upid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 45052 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
