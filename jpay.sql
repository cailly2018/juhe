/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50553
Source Host           : localhost:3306
Source Database       : jpay

Target Server Type    : MYSQL
Target Server Version : 50553
File Encoding         : 65001

Date: 2018-09-28 16:01:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for pay_admin
-- ----------------------------
DROP TABLE IF EXISTS `pay_admin`;
CREATE TABLE `pay_admin` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '管理员ID',
  `username` varchar(50) NOT NULL COMMENT '后台用户名',
  `password` varchar(32) NOT NULL COMMENT '后台用户密码',
  `groupid` tinyint(1) unsigned DEFAULT '0' COMMENT '用户组',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `google_secret_key` varchar(128) NOT NULL DEFAULT '' COMMENT '谷歌令牌密钥',
  `mobile` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号码',
  `session_random` varchar(50) NOT NULL DEFAULT '' COMMENT 'session随机字符串',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_admin
-- ----------------------------
INSERT INTO `pay_admin` VALUES ('1', 'admin', '5c19392c28bd0d4b46527ffe32306b28', '1', '0', '3RJZWYYUNAFY4URF', '', 'OPVz4daZoZe3ASHdfkoKPhXaYPSAdwbT');

-- ----------------------------
-- Table structure for pay_apimoney
-- ----------------------------
DROP TABLE IF EXISTS `pay_apimoney`;
CREATE TABLE `pay_apimoney` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `payapiid` int(11) DEFAULT NULL,
  `money` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `freezemoney` decimal(15,3) NOT NULL DEFAULT '0.000' COMMENT '冻结金额',
  `status` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_apimoney
-- ----------------------------
INSERT INTO `pay_apimoney` VALUES ('10', '6', '207', '18000.0000', '0.000', '1');

-- ----------------------------
-- Table structure for pay_article
-- ----------------------------
DROP TABLE IF EXISTS `pay_article`;
CREATE TABLE `pay_article` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `catid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '分类ID',
  `groupid` tinyint(1) NOT NULL DEFAULT '0' COMMENT '分组 0：所有 1：商户 2：代理',
  `title` varchar(300) NOT NULL COMMENT '标题',
  `content` text COMMENT '内容',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL COMMENT '描述',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1显示 0 不显示',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_article
-- ----------------------------

-- ----------------------------
-- Table structure for pay_attachment
-- ----------------------------
DROP TABLE IF EXISTS `pay_attachment`;
CREATE TABLE `pay_attachment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `filename` varchar(100) NOT NULL,
  `path` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_attachment
-- ----------------------------
INSERT INTO `pay_attachment` VALUES ('48', '2', '242dd42a2834349b88359f1eccea15ce36d3be7e.jpg', 'Uploads/verifyinfo/59a2b65d0816c.jpg');
INSERT INTO `pay_attachment` VALUES ('46', '2', '6-140F316125V44.jpg', 'Uploads/verifyinfo/59a2b65cd9877.jpg');
INSERT INTO `pay_attachment` VALUES ('47', '2', '6-140F316132J02.jpg', 'Uploads/verifyinfo/59a2b65cea2ec.jpg');

-- ----------------------------
-- Table structure for pay_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `pay_auth_group`;
CREATE TABLE `pay_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_manager` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1需要验证权限 0 不需要验证权限',
  `rules` varchar(500) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_auth_group
-- ----------------------------
INSERT INTO `pay_auth_group` VALUES ('1', '超级管理员', '1', '0', '1,49,2,3,51,4,57,5,55,56,58,59,6,43,44,52,53,48,70,54,7,8,60,61,62,9,63,64,65,66,10,67,68,69,11,12,79,80,81,82,83,84,85,86,87,88,89,90,91,93,94,95,96,97,98,99,100,101,13,14,15,92,16,73,76,77,78,17,18,19,71,75,20,21,72,74,22,23,24,25,26,46,27,28,108,29,102,30,103,107,104,105,109,110,111,31,32,33,34,35,36,37,38,39,40,41,42,45,47,116,117,118');
INSERT INTO `pay_auth_group` VALUES ('2', '运营管理员', '1', '0', '1,77,3,18,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,49,92,93,94,98,99,19,50,51,52,4,14,54,55,56,57,15,59,60,61,62,63,5,23,65,66,24,67,6,13,68,69,70,71,73,76,7,12,78,79,80,81,82,22,83,84,85,86,87');
INSERT INTO `pay_auth_group` VALUES ('3', '财务管理员', '1', '1', '1,77,5,23,65,66,24,67,6,13,68,69,70,71,73,76,25,72,26,74,75');
INSERT INTO `pay_auth_group` VALUES ('4', '普通商户', '1', '1', '');
INSERT INTO `pay_auth_group` VALUES ('5', '普通代理商', '1', '1', '1,77,2,8,9,89,101,19,50,51,52,53,4,15,60,61,62,64,6,13,68,69,70,71,73,76,25,72,26,74,75,104,102,103,105');
INSERT INTO `pay_auth_group` VALUES ('6', '西安', '2', '1', '1,49,27,29,102');
INSERT INTO `pay_auth_group` VALUES ('7', 'tie', '2', '1', '1,49,77,27,30,103,106,107,119');

-- ----------------------------
-- Table structure for pay_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `pay_auth_group_access`;
CREATE TABLE `pay_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_auth_group_access
-- ----------------------------
INSERT INTO `pay_auth_group_access` VALUES ('1', '1');
INSERT INTO `pay_auth_group_access` VALUES ('7', '2');

-- ----------------------------
-- Table structure for pay_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `pay_auth_rule`;
CREATE TABLE `pay_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(100) DEFAULT '' COMMENT '图标',
  `menu_name` varchar(100) NOT NULL DEFAULT '' COMMENT '规则唯一标识Controller/action',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '菜单名称',
  `pid` tinyint(5) NOT NULL DEFAULT '0' COMMENT '菜单ID ',
  `is_menu` tinyint(1) unsigned DEFAULT '0' COMMENT '1:是主菜单 0否',
  `is_race_menu` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1:是 0:不是',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=120 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of pay_auth_rule
-- ----------------------------
INSERT INTO `pay_auth_rule` VALUES ('1', 'fa fa-home', 'Index/index', '管理首页', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('2', 'fa fa-cogs', 'System/#', '系统设置', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('3', 'fa fa-cog', 'System/base', '基本设置', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('4', 'fa fa-envelope-o', 'System/email', '邮件设置', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('5', 'fa fa-send', 'System/smssz', '短信设置', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('6', 'fa fa-hourglass', 'System/planning', '计划任务', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('7', 'fa fa-user-circle', 'Admin/#', '管理员管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('8', 'fa fa-vcard ', 'Admin/index', '管理员信息', '7', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('9', 'fa fa-street-view', 'Auth/index', '角色配置', '7', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('10', 'fa fa-universal-access', 'Menu/index', '权限配置', '7', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('11', 'fa fa-users', 'User/#', '用户管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('12', 'fa fa-user', 'User/index?status=1&authorized=1', '已认证用户', '11', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('13', 'fa fa-user-o', 'User/index?status=1&authorized=2', '待认证用户', '11', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('14', 'fa fa-user-plus', 'User/index?status=1&authorized=0', '未认证用户', '11', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('15', 'fa fa-user-times', 'User/index?status=0', '冻结用户', '11', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('16', 'fa fa-gift', 'User/invitecode', '邀请码', '11', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('17', 'fa fa-address-book', 'User/loginrecord', '登录记录', '11', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('18', 'fa fa-handshake-o', 'Agent/#', '代理管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('19', 'fa fa-hand-lizard-o', 'User/agentList', '代理列表', '18', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('20', 'fa fa-signing', 'Order/changeRecord?bank=9', '佣金记录', '18', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('21', 'fa fa-sellsy', 'Order/dfApiOrderList', '代付Api订单', '18', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('22', 'fa fa-reorder', 'User/#', '订单管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('23', 'fa fa-indent', 'Order/changeRecord', '流水记录', '22', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('24', 'fa fa-thumbs-up', 'Order/index?status=1or2', '成功订单', '22', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('25', 'fa fa-thumbs-down', 'Order/index?status=0', '未支付订单', '22', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('26', 'fa fa-hand-o-right', 'Order/index?status=1', '异常订单', '22', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('27', 'fa fa-user-secret', 'Withdrawal', '提款管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('28', 'fa fa-wrench', 'Withdrawal/setting', '提款设置', '27', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('29', 'fa fa-asl-interpreting', 'Withdrawal/index', '手动结算', '27', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('30', 'fa fa-window-restore', 'Withdrawal/payment', '代付结算', '27', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('31', 'fa fa-bank', 'Channel/#', '通道管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('32', 'fa fa-product-hunt', 'Channel/index', '入金渠道设置', '31', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('33', 'fa fa-sitemap', 'Channel/product', '支付产品设置', '31', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('34', 'fa fa-sliders', 'PayForAnother/index', '代付渠道设置', '31', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('35', 'fa fa-book', 'Content/#', '文章管理', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('36', 'fa fa-tags', 'Content/category', '栏目列表', '35', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('37', 'fa fa-list-alt', 'Content/article', '文章列表', '35', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('38', 'fa fa-line-chart', 'Statistics/#', '财务分析', '0', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('39', 'fa fa-bar-chart-o', 'Statistics/index', '交易统计', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('40', 'fa fa-area-chart', 'Statistics/userFinance', '商户交易统计', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('41', 'fa fa-industry', 'Statistics/userFinance?groupid=agent', '代理商交易统计', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('42', 'fa fa-pie-chart', 'Statistics/channelFinance', '接口交易统计', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('43', 'fa fa-cubes', 'Template/index', '模板设置', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('44', 'fa fa-mobile', 'System/mobile', '手机设置', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('45', 'fa fa-signal', 'Statistics/chargeRank', '充值排行榜', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('46', 'fa fa-first-order', 'Deposit/index', '投诉保证金设置', '22', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('47', 'fa fa-asterisk', 'Statistics/complaintsDeposit', '投诉保证金统计', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('48', 'fa fa-database', 'System/clearData', '数据清理', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('49', '', 'Index/main', 'Dashboard', '1', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('51', '', 'System/SaveBase', '保存设置', '3', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('52', '', 'System/BindMobileShow', '绑定手机号码', '44', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('53', '', 'System/editMobileShow', '手机修改', '44', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('54', '', 'System/editPassword', '修改密码', '2', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('55', '', 'System/editSmstemplate', '短信模板', '5', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('56', '', 'System/saveSmstemplate', '保存短信模板', '5', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('57', '', 'System/saveEmail', '邮件保存', '4', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('58', '', 'System/testMobile', '测试短信', '5', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('59', '', 'System/deleteAdmin', '删除短信模板', '5', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('60', '', 'Admin/addAdmin', '管理员添加', '8', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('61', '', 'Admin/editAdmin', '管理员修改', '8', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('62', '', 'Admin/deleteAdmin', '管理员删除', '8', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('63', '', 'Auth/addGroup', '添加角色', '9', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('64', '', 'Auth/editGroup', '修改角色', '9', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('65', '', 'Auth/giveRole', '选择角色', '9', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('66', '', 'Auth/ruleGroup', '分配权限', '9', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('67', '', 'Menu/addMenu', '添加菜单', '10', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('68', '', 'Menu/editMenu', '修改菜单', '10', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('69', '', 'Menu/delMenu', '删除菜单', '10', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('70', '', 'System/clearDataSend', '数据清理提交', '48', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('71', '', 'User/addAgentCate', '代理级别', '19', '0', '0', '1', '0', '');
INSERT INTO `pay_auth_rule` VALUES ('72', '', 'User/saveAgentCate', '保存代理级别', '18', '1', '0', '1', '0', '');
INSERT INTO `pay_auth_rule` VALUES ('73', '', 'User/addInvitecode', '添加激活码', '16', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('74', '', 'User/EditAgentCate', '修改代理分类', '18', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('75', '', 'User/deleteAgentCate', '删除代理分类', '19', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('76', '', 'User/setInvite', '邀请码设置', '16', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('77', '', 'User/addInvite', '创建邀请码', '16', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('78', '', 'User/delInvitecode', '删除邀请码', '16', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('79', '', 'User/editUser', '用户编辑', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('80', '', 'User/changeuser', '修改状态', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('81', '', 'User/authorize', '用户认证', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('82', '', 'User/usermoney', '用户资金管理', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('83', '', 'User/userWithdrawal', '用户提现设置', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('84', '', 'User/userRateEdit', '用户费率设置', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('85', '', 'User/editPassword', '用户密码修改', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('86', '', 'User/editStatus', '用户状态修改', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('87', '', 'User/delUser', '用户删除', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('88', '', 'User/thawingFunds', 'T1解冻任务管理', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('89', '', 'User/exportuser', '导出用户', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('90', '', 'User/editAuthoize', '修改用户认证', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('91', '', 'User/getRandstr', '切换商户密钥', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('92', '', 'User/suoding', '用户锁定', '15', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('93', '', 'User/editbankcard', '银行卡管理', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('94', '', 'User/saveUser', '添加用户', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('95', '', 'User/saveUserProduct', '保存用户产品', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('96', '', 'User/saveUserRate', '保存用户费率', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('97', '', 'User/edittongdao', '编辑通道', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('98', '', 'User/frozenMoney', '用户资金冻结', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('99', '', 'User/unfrozenHandles', 'T1资金解冻', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('100', '', 'User/frozenOrder', '冻结订单列表', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('101', '', 'User/frozenHandles', 'T1订单解冻展示', '12', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('102', '', 'Withdrawal/editStatus', '操作状态', '29', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('103', '', 'Withdrawal/editwtStatus', '操作订单状态', '30', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('104', '', 'Withdrawal/exportorder', '导出数据', '27', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('105', '', 'Withdrawal/editwtAllStatus', '批量修改提款状态', '27', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('106', '', 'Withdrawal/exportweituo', '导出委托提现', '30', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('107', '', 'Payment/index', '提交上游', '30', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('108', '', 'Withdrawal/saveWithdrawal', '保存设置', '28', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('109', '', 'Withdrawal/AddHoliday', '添加假日', '27', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('110', '', 'Withdrawal/settimeEdit', '编辑提款时间', '27', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('111', '', 'Withdrawal/delHoliday', '删除节假日', '27', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('112', '', 'Statistics/exportorder', '订单数据导出', '40', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('113', '', 'Statistics/details', '查看详情', '39', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('114', '', 'Order/exportorder', '订单导出', '23', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('115', '', 'Order/exceldownload', '记录导出', '23', '0', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('116', 'fa fa-area-chart', 'Statistics/platformReport', '平台报表', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('117', 'fa fa-area-chart', 'Statistics/merchantReport', '商户报表', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('118', 'fa fa-area-chart', 'Statistics/agentReport', '代理报表', '38', '1', '0', '1', '1', '');
INSERT INTO `pay_auth_rule` VALUES ('119', '', 'Withdrawal/submitDf', '代付提交', '30', '0', '0', '1', '1', '');

-- ----------------------------
-- Table structure for pay_auto_df_log
-- ----------------------------
DROP TABLE IF EXISTS `pay_auto_df_log`;
CREATE TABLE `pay_auto_df_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `df_id` int(11) NOT NULL DEFAULT '0' COMMENT '代付ID',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '类型：1提交 2查询',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '结果 0：提交失败 1：提交成功 2：代付成功 3：代付失败',
  `msg` varchar(255) DEFAULT '' COMMENT '描述',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '提交时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_auto_df_log
-- ----------------------------

-- ----------------------------
-- Table structure for pay_auto_unfrozen_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_auto_unfrozen_order`;
CREATE TABLE `pay_auto_unfrozen_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `freeze_money` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '冻结金额',
  `unfreeze_time` int(11) NOT NULL DEFAULT '0' COMMENT '计划解冻时间',
  `real_unfreeze_time` int(11) NOT NULL DEFAULT '0' COMMENT '实际解冻时间',
  `is_pause` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否暂停解冻 0正常解冻 1暂停解冻',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '解冻状态 0未解冻 1已解冻',
  `create_at` int(11) NOT NULL COMMENT '记录创建时间',
  `update_at` int(11) NOT NULL COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_unfreezeing` (`status`,`is_pause`,`unfreeze_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='手动冻结订单';

-- ----------------------------
-- Records of pay_auto_unfrozen_order
-- ----------------------------

-- ----------------------------
-- Table structure for pay_bankcard
-- ----------------------------
DROP TABLE IF EXISTS `pay_bankcard`;
CREATE TABLE `pay_bankcard` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `bankname` varchar(100) NOT NULL COMMENT '银行名称',
  `subbranch` varchar(100) NOT NULL COMMENT '支行名称',
  `accountname` varchar(100) NOT NULL COMMENT '开户名',
  `cardnumber` varchar(100) NOT NULL COMMENT '银行卡号',
  `province` varchar(100) NOT NULL COMMENT '所属省',
  `city` varchar(100) NOT NULL COMMENT '所属市',
  `ip` varchar(100) DEFAULT NULL COMMENT '上次修改IP',
  `ipaddress` varchar(300) DEFAULT NULL COMMENT 'IP地址',
  `alias` varchar(255) DEFAULT '' COMMENT '备注',
  `isdefault` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认 1是 0 否',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `IND_UID` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=203 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_bankcard
-- ----------------------------
INSERT INTO `pay_bankcard` VALUES ('202', '51', '东亚银行', '阿斯达', '阿斯顿', '23423423', '阿萨德', '位', null, null, '1', '0', '0');

-- ----------------------------
-- Table structure for pay_blockedlog
-- ----------------------------
DROP TABLE IF EXISTS `pay_blockedlog`;
CREATE TABLE `pay_blockedlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderid` varchar(100) NOT NULL COMMENT '订单号',
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `amount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '冻结金额',
  `thawtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '解冻时间',
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '商户支付通道',
  `createtime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1 解冻 0 冻结',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资金冻结待解冻记录';

-- ----------------------------
-- Records of pay_blockedlog
-- ----------------------------

-- ----------------------------
-- Table structure for pay_browserecord
-- ----------------------------
DROP TABLE IF EXISTS `pay_browserecord`;
CREATE TABLE `pay_browserecord` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `articleid` int(11) NOT NULL DEFAULT '0',
  `userid` int(11) NOT NULL DEFAULT '0',
  `datetime` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_browserecord
-- ----------------------------

-- ----------------------------
-- Table structure for pay_category
-- ----------------------------
DROP TABLE IF EXISTS `pay_category`;
CREATE TABLE `pay_category` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '父级ID',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='文章栏目';

-- ----------------------------
-- Records of pay_category
-- ----------------------------
INSERT INTO `pay_category` VALUES ('1', '最新资讯', '0', '1');
INSERT INTO `pay_category` VALUES ('2', '公司新闻', '0', '1');
INSERT INTO `pay_category` VALUES ('3', '公告通知', '0', '1');
INSERT INTO `pay_category` VALUES ('4', '站内公告', '3', '1');
INSERT INTO `pay_category` VALUES ('5', '公司新闻', '3', '1');

-- ----------------------------
-- Table structure for pay_channel
-- ----------------------------
DROP TABLE IF EXISTS `pay_channel`;
CREATE TABLE `pay_channel` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '供应商通道ID',
  `code` varchar(200) DEFAULT NULL COMMENT '供应商通道英文编码',
  `title` varchar(200) DEFAULT NULL COMMENT '供应商通道名称',
  `mch_id` varchar(100) DEFAULT NULL COMMENT '商户号',
  `signkey` varchar(500) DEFAULT NULL COMMENT '签文密钥',
  `appid` varchar(100) DEFAULT NULL COMMENT '应用APPID',
  `appsecret` varchar(100) DEFAULT NULL COMMENT '安全密钥',
  `gateway` varchar(300) DEFAULT NULL COMMENT '网关地址',
  `pagereturn` varchar(255) DEFAULT NULL COMMENT '页面跳转网址',
  `serverreturn` varchar(255) DEFAULT NULL COMMENT '服务器通知网址',
  `defaultrate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '下家费率',
  `fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '封顶手续费',
  `rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '银行费率',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次更改时间',
  `unlockdomain` varchar(100) NOT NULL COMMENT '防封域名',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '渠道类型: 1 微信扫码 2 微信H5 3 支付宝扫码 4 支付宝H5 5网银跳转 6网银直连 7百度钱包 8 QQ钱包 9 京东钱包',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `paying_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天交易金额',
  `all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天上游可交易量',
  `last_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后交易时间',
  `min_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最小交易额',
  `max_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最大交易额',
  `control_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '风控状态:0否1是',
  `offline_status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '通道上线状态:0已下线，1上线',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=243 DEFAULT CHARSET=utf8 COMMENT='供应商列表';

-- ----------------------------
-- Records of pay_channel
-- ----------------------------
INSERT INTO `pay_channel` VALUES ('199', 'WxSm', '微信扫码支付', '', '', '', '', '', '', '', '0.0400', '0.0900', '0.0000', '1503846107', '', '0', '1', '1', '3', '0.00', '0.00', '0', '10.00', '100.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('200', 'WxGzh', '微信H5', '', '', 'wxf33668d58442ff6e', '', '', '', '', '0.0000', '0.0000', '0.0000', '1502378687', '', '0', '2', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('201', 'Aliscan', '支付宝扫码', '', '', '', '', '', '', '', '0.0000', '0.0000', '0.0000', '1503857975', '', '1', '3', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('202', 'Aliwap', '支付宝H5', '', '', '', '', '', '', '', '0.0000', '0.0000', '0.0000', '1503857966', '', '1', '4', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('203', 'QQSCAN', 'QQ扫码', '', '', '', '', '', '', '', '0.0050', '0.0000', '0.0000', '1503280494', '', '0', '8', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('207', 'Test', '测试支付', '', '', '', '', '', '', '', '0.0000', '0.0000', '0.0000', '1522845914', '', '1', '1', '0', '3', '200.00', '200.00', '1523033452', '10.00', '100.00', '1', '1');
INSERT INTO `pay_channel` VALUES ('208', 'WftAliH5', '威富通支付（支付宝H5）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523153345', '', '0', '4', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('209', 'WftAliSm', '威富通支付（支付宝扫码）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523153409', '', '0', '3', '0', '0', '0.00', '5.00', '0', '0.00', '0.00', '1', '1');
INSERT INTO `pay_channel` VALUES ('210', 'WftWxWap', '威富通支付（微信H5）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523153467', '', '0', '2', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('211', 'WftWxSm', '威富通支付（微信扫码）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523153519', '', '0', '1', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('212', 'WftQQWap', '威富通支付（QQH5）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523153579', '', '0', '10', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('213', 'WftQQSm', '威富通支付（QQ扫码）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523153602', '', '0', '8', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('216', 'WftWxJsapi', '威付通公众号支付', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1523951537', '', '0', '2', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('242', 'LeGuoAli', '银行支付宝扫码h5', '', '', '', '', 'http://www.surujin.com/Home/Pay/payFor', '', '', '0.0000', '0.0000', '0.0000', '1534313830', '', '1', '3', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('220', 'WftWxJspay', '威富通支付（微信公众号支付）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1525870269', '', '0', '2', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('221', 'WftAliJspay', '威富通支付（支付宝服务窗支付）', '', '', '', '', 'https://pay.swiftpass.cn/pay/gateway', '', '', '0.0000', '0.0000', '0.0000', '1525924083', '', '0', '4', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('222', 'YeeBank', '易宝网银支付', '', '', '', '', 'https://open.yeepay.com/yop-center', '', '', '0.0000', '0.0000', '0.0000', '1526711871', 'http://pay.honor123.com', '0', '5', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('223', 'YeeQuick', '易宝快捷支付', '', '', '', '', 'https://open.yeepay.com/yop-center', '', '', '0.0000', '0.0000', '0.0000', '1526711865', 'http://pay.honor123.com', '0', '5', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('225', 'YeeQuick', '测试快捷支付', '', '', '', '', 'https://open.yeepay.com/yop-center', '', '', '0.0000', '0.0000', '0.0000', '1529554569', 'http://pay.honor123.com', '0', '5', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('226', 'YeeBank', '测试网关', '', '', '', '', 'https://open.yeepay.com/yop-center', '', '', '0.0000', '0.0000', '0.0000', '1529554595', 'http://pay.honor123.com', '0', '5', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');
INSERT INTO `pay_channel` VALUES ('236', 'LeGuoAli', '收银台—测试—支付宝', '', '', '', '', 'http://www.surujin.com/Home/Pay/payFor', '', '', '0.0000', '0.0000', '0.0000', '1534313840', '', '1', '3', '0', '0', '0.00', '0.00', '0', '0.00', '0.00', '0', '1');

-- ----------------------------
-- Table structure for pay_channel_account
-- ----------------------------
DROP TABLE IF EXISTS `pay_channel_account`;
CREATE TABLE `pay_channel_account` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT COMMENT '供应商通道账号ID',
  `channel_id` smallint(6) unsigned NOT NULL COMMENT '通道id',
  `mch_id` varchar(100) DEFAULT NULL COMMENT '商户号',
  `signkey` varchar(500) DEFAULT NULL COMMENT '签文密钥',
  `appid` varchar(100) DEFAULT NULL COMMENT '应用APPID',
  `appsecret` varchar(100) DEFAULT NULL COMMENT '安全密钥',
  `defaultrate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '下家费率',
  `fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '封顶手续费',
  `rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '银行费率',
  `updatetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '上次更改时间',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  `title` varchar(100) DEFAULT NULL COMMENT '账户标题',
  `weight` tinyint(2) DEFAULT NULL COMMENT '轮询权重',
  `custom_rate` tinyint(1) DEFAULT NULL COMMENT '是否自定义费率',
  `start_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始交易时间',
  `end_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `last_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后一笔交易时间',
  `paying_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天交易金额',
  `all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单日可交易金额',
  `max_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔交易最大金额',
  `min_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔交易最小金额',
  `offline_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '上线状态-1上线,0下线',
  `control_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '风控状态-0不风控,1风控中',
  `is_defined` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否自定义:1-是,0-否',
  `unit_frist_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间第一笔交易时间',
  `unit_paying_number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '单时间交易笔数',
  `unit_paying_amount` decimal(11,0) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间交易金额',
  `unit_interval` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间数值',
  `time_unit` char(1) NOT NULL DEFAULT 's' COMMENT '限制时间单位',
  `unit_number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间次数',
  `unit_all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单位时间金额',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=403 DEFAULT CHARSET=utf8 COMMENT='供应商账号列表';

-- ----------------------------
-- Records of pay_channel_account
-- ----------------------------
INSERT INTO `pay_channel_account` VALUES ('218', '199', '', '', '', '', '0.0400', '0.0900', '0.0000', '1513408073', '1', '微信扫码支付', '1', '0', '0', '0', '0', '0.00', '0.00', '50.00', '1.00', '0', '0', '1', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('219', '200', '', '', 'wxf33668d58442ff6e', '', '0.0000', '0.0000', '0.0000', '1513408073', '1', '微信H5', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('220', '201', '', '', '', '', '0.0000', '0.0000', '0.0000', '1513408073', '1', '支付宝扫码', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('221', '202', '', '', '', '', '0.0000', '0.0000', '0.0000', '1513408073', '1', '支付宝H5', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('222', '203', '', '', '', '', '0.0050', '0.0000', '0.0000', '1513408073', '1', 'QQ扫码', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('223', '207', '测试商户号', '测试证书密钥', '', '', '0.0000', '0.0000', '0.0000', '1516441979', '1', '测试名称', '1', '0', '0', '0', '1523033928', '1000.00', '1000.00', '0.00', '0.00', '1', '1', '1', '1523033928', '1', '100', '1', 'i', '10', '500.00');
INSERT INTO `pay_channel_account` VALUES ('224', '208', '105560103567', 'a170cd72b781fc061f867092e50f313e', '', '', '0.0000', '0.0000', '0.0000', '1524728551', '1', '威富通支付（支付宝H5）', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('225', '209', '102575562151', '', '', '', '0.0000', '0.0000', '0.0000', '1525870052', '1', '102575562151', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('244', '220', '102575562151', '', 'wx24fefb923f9b6fd8', 'f38a5f45e12d2c2116eb697ec14d1495', '0.0000', '0.0000', '0.0000', '1525918488', '1', '102575562151', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('226', '211', '102575562151', '', '', '', '0.0000', '0.0000', '0.0000', '1525868551', '1', '102575562151', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('227', '213', '102575562151', '', '', '', '0.0000', '0.0000', '0.0000', '1525870019', '1', '102575562151', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('235', '216', '105560103567', 'a170cd72b781fc061f867092e50f313e', '', '', '0.0000', '0.0000', '0.0000', '1524728693', '1', '105560103567', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('241', '212', '105560103567', 'a170cd72b781fc061f867092e50f313e', '', '', '0.0000', '0.0000', '0.0000', '1524728207', '1', '105560103567', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('242', '210', '105560103567', 'a170cd72b781fc061f867092e50f313e', '', '', '0.0000', '0.0000', '0.0000', '1524728648', '1', '105560103567', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('245', '221', '102575562151', '', '2018051060122279', '', '0.0000', '0.0000', '0.0000', '1526005377', '1', '102575562151', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('246', '222', '10021991067', '', 'OPR:10021991067', '', '0.0000', '0.0000', '0.0000', '1526711770', '1', '10021991067', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('247', '223', '10021991067', '', 'OPR:10021991067', '', '0.0000', '0.0000', '0.0000', '1526711748', '1', '10021991067', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('249', '225', '10021991067', '', 'OPR:10021991067', '', '0.0000', '0.0000', '0.0000', '1526707462', '1', '10021991067', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('250', '226', '10021991067', '', 'OPR:10021991067', '', '0.0000', '0.0000', '0.0000', '1526707512', '0', '10021991067', '1', '0', '0', '0', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '0', '0', 's', '0', '0.00');
INSERT INTO `pay_channel_account` VALUES ('402', '236', '100038', '973fc799970fc24ce9ad82f9e87d7026f4bc648a', '', '', '0.0000', '0.0000', '0.0000', '1534319645', '1', '010admin001@qq.com', '1', '0', '0', '0', '0', '0.00', '46000.00', '3000.00', '0.00', '0', '0', '1', '0', '0', '0', '0', 's', '0', '0.00');

-- ----------------------------
-- Table structure for pay_complaints_deposit
-- ----------------------------
DROP TABLE IF EXISTS `pay_complaints_deposit`;
CREATE TABLE `pay_complaints_deposit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `pay_orderid` varchar(100) NOT NULL DEFAULT '0' COMMENT '系统订单号',
  `out_trade_id` varchar(50) NOT NULL DEFAULT '' COMMENT '下游订单号',
  `freeze_money` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '冻结保证金额',
  `unfreeze_time` int(11) NOT NULL DEFAULT '0' COMMENT '计划解冻时间',
  `real_unfreeze_time` int(11) NOT NULL DEFAULT '0' COMMENT '实际解冻时间',
  `is_pause` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否暂停解冻 0正常解冻 1暂停解冻',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '解冻状态 0未解冻 1已解冻',
  `create_at` int(11) NOT NULL COMMENT '记录创建时间',
  `update_at` int(11) NOT NULL COMMENT '记录更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_unfreezeing` (`status`,`is_pause`,`unfreeze_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投诉保证金余额';

-- ----------------------------
-- Records of pay_complaints_deposit
-- ----------------------------

-- ----------------------------
-- Table structure for pay_complaints_deposit_rule
-- ----------------------------
DROP TABLE IF EXISTS `pay_complaints_deposit_rule`;
CREATE TABLE `pay_complaints_deposit_rule` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  `is_system` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否系统规则 1是 0否',
  `ratio` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '投诉保证金比例（百分比）',
  `freeze_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '冻结时间（秒）',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '规则是否开启 1开启 0关闭',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='投诉保证金规则表';

-- ----------------------------
-- Records of pay_complaints_deposit_rule
-- ----------------------------

-- ----------------------------
-- Table structure for pay_df_api_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_df_api_order`;
CREATE TABLE `pay_df_api_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `trade_no` varchar(30) NOT NULL DEFAULT '' COMMENT '平台订单号',
  `out_trade_no` varchar(30) NOT NULL DEFAULT '' COMMENT '商户订单号',
  `money` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '金额',
  `bankname` varchar(100) NOT NULL DEFAULT '' COMMENT '银行名称',
  `subbranch` varchar(100) NOT NULL DEFAULT '' COMMENT '支行名称',
  `accountname` varchar(100) NOT NULL DEFAULT '' COMMENT '开户名',
  `cardnumber` varchar(100) NOT NULL DEFAULT '' COMMENT '银行卡号',
  `province` varchar(100) NOT NULL DEFAULT '' COMMENT '所属省',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '所属市',
  `ip` varchar(100) DEFAULT '' COMMENT 'IP地址',
  `check_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：待审核 1：已提交后台审核 2：审核驳回',
  `extends` text COMMENT '扩展字段',
  `df_id` int(11) NOT NULL DEFAULT '0' COMMENT '代付ID',
  `notifyurl` varchar(255) DEFAULT '' COMMENT '异步通知地址',
  `reject_reason` varchar(255) NOT NULL DEFAULT '' COMMENT '驳回原因',
  `check_time` int(11) NOT NULL DEFAULT '0' COMMENT '审核时间',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `IND_UID` (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_df_api_order
-- ----------------------------

-- ----------------------------
-- Table structure for pay_email
-- ----------------------------
DROP TABLE IF EXISTS `pay_email`;
CREATE TABLE `pay_email` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `smtp_host` varchar(300) DEFAULT NULL,
  `smtp_port` varchar(300) DEFAULT NULL,
  `smtp_user` varchar(300) DEFAULT NULL,
  `smtp_pass` varchar(300) DEFAULT NULL,
  `smtp_email` varchar(300) DEFAULT NULL,
  `smtp_name` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_email
-- ----------------------------

-- ----------------------------
-- Table structure for pay_invitecode
-- ----------------------------
DROP TABLE IF EXISTS `pay_invitecode`;
CREATE TABLE `pay_invitecode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invitecode` varchar(32) NOT NULL,
  `fmusernameid` int(11) unsigned NOT NULL DEFAULT '0',
  `syusernameid` int(11) NOT NULL DEFAULT '0',
  `regtype` tinyint(1) unsigned NOT NULL DEFAULT '4' COMMENT '用户组 4 普通用户 5 代理商',
  `fbdatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `yxdatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `sydatetime` int(11) unsigned DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '邀请码状态 0 禁用 1 未使用 2 已使用',
  `is_admin` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否管理员添加',
  PRIMARY KEY (`id`),
  UNIQUE KEY `invitecode` (`invitecode`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_invitecode
-- ----------------------------

-- ----------------------------
-- Table structure for pay_inviteconfig
-- ----------------------------
DROP TABLE IF EXISTS `pay_inviteconfig`;
CREATE TABLE `pay_inviteconfig` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invitezt` tinyint(1) unsigned DEFAULT '1',
  `invitetype2number` int(11) NOT NULL DEFAULT '20',
  `invitetype2ff` smallint(6) NOT NULL DEFAULT '1',
  `invitetype5number` int(11) NOT NULL DEFAULT '20',
  `invitetype5ff` smallint(6) NOT NULL DEFAULT '1',
  `invitetype6number` int(11) NOT NULL DEFAULT '20',
  `invitetype6ff` smallint(6) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_inviteconfig
-- ----------------------------
INSERT INTO `pay_inviteconfig` VALUES ('1', '1', '0', '0', '100', '0', '0', '0');

-- ----------------------------
-- Table structure for pay_loginrecord
-- ----------------------------
DROP TABLE IF EXISTS `pay_loginrecord`;
CREATE TABLE `pay_loginrecord` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0',
  `logindatetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `loginip` varchar(100) NOT NULL,
  `loginaddress` varchar(300) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型：0：前台用户 1：后台用户',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_loginrecord
-- ----------------------------
INSERT INTO `pay_loginrecord` VALUES ('1', '47', '2018-08-11 23:33:47', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('2', '47', '2018-08-11 23:34:44', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('3', '47', '2018-08-11 23:36:38', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('4', '47', '2018-08-11 23:45:27', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('5', '47', '2018-08-11 23:46:45', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('6', '47', '2018-08-11 23:49:13', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('7', '47', '2018-08-11 23:54:39', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('8', '47', '2018-08-11 23:54:51', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('9', '47', '2018-08-12 00:10:05', '42.249.2.33', '辽宁-', '0');
INSERT INTO `pay_loginrecord` VALUES ('10', '47', '2018-08-12 00:51:58', '112.224.67.184', '山东-青岛', '0');
INSERT INTO `pay_loginrecord` VALUES ('11', '47', '2018-08-12 00:53:15', '110.54.221.19', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('12', '47', '2018-08-12 02:39:50', '110.54.221.19', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('13', '47', '2018-08-12 14:02:06', '110.54.201.138', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('14', '47', '2018-08-13 01:37:08', '110.54.217.51', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('15', '47', '2018-08-13 11:22:24', '103.12.88.66', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('16', '47', '2018-08-13 11:25:12', '112.207.1.37', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('17', '47', '2018-08-13 11:25:13', '112.207.1.37', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('18', '47', '2018-08-13 11:25:14', '112.207.1.37', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('19', '47', '2018-08-13 11:25:16', '112.207.1.37', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('20', '47', '2018-08-13 15:03:03', '103.12.88.66', '菲律宾-', '0');
INSERT INTO `pay_loginrecord` VALUES ('21', '1', '2018-08-14 14:12:51', '42.249.13.78', '辽宁省-电信', '1');
INSERT INTO `pay_loginrecord` VALUES ('22', '1', '2018-08-14 14:13:04', '183.234.108.122', '广东省-移动', '1');
INSERT INTO `pay_loginrecord` VALUES ('23', '1', '2018-08-14 14:13:59', '183.234.108.122', '广东省-移动', '1');
INSERT INTO `pay_loginrecord` VALUES ('24', '1', '2018-08-14 14:28:32', '183.234.108.122', '广东省-移动', '1');
INSERT INTO `pay_loginrecord` VALUES ('25', '1', '2018-08-14 15:19:06', '123.185.159.113', '辽宁省大连市-电信ADSL', '1');
INSERT INTO `pay_loginrecord` VALUES ('26', '51', '2018-08-14 15:32:00', '123.185.159.113', '辽宁-大连', '0');
INSERT INTO `pay_loginrecord` VALUES ('27', '50', '2018-08-14 16:51:32', '112.97.60.108', '广东-深圳', '0');
INSERT INTO `pay_loginrecord` VALUES ('28', '1', '2018-08-14 16:54:19', '112.97.60.108', '广东省深圳市-联通', '1');
INSERT INTO `pay_loginrecord` VALUES ('29', '1', '2018-08-14 17:02:27', '49.223.208.186', '山东省青岛市-长城宽带', '1');
INSERT INTO `pay_loginrecord` VALUES ('30', '50', '2018-08-14 17:04:36', '49.223.208.186', '辽宁-大连', '0');
INSERT INTO `pay_loginrecord` VALUES ('31', '50', '2018-08-14 17:05:31', '49.223.208.186', '辽宁-大连', '0');
INSERT INTO `pay_loginrecord` VALUES ('32', '50', '2018-08-14 18:47:37', '112.97.56.108', '广东-深圳', '0');
INSERT INTO `pay_loginrecord` VALUES ('33', '50', '2018-08-14 19:20:23', '115.49.166.144', '河南-漯河', '0');
INSERT INTO `pay_loginrecord` VALUES ('34', '50', '2018-08-14 19:21:46', '115.49.166.144', '河南-漯河', '0');
INSERT INTO `pay_loginrecord` VALUES ('35', '47', '2018-08-15 09:36:10', '115.49.166.144', '河南-漯河', '0');
INSERT INTO `pay_loginrecord` VALUES ('36', '1', '2018-08-15 14:16:24', '123.185.159.113', '辽宁省大连市-电信ADSL', '1');
INSERT INTO `pay_loginrecord` VALUES ('37', '1', '2018-08-15 14:22:07', '114.88.77.236', '上海市-电信', '1');
INSERT INTO `pay_loginrecord` VALUES ('38', '54', '2018-08-15 14:36:54', '114.88.77.236', '上海-上海', '0');
INSERT INTO `pay_loginrecord` VALUES ('39', '1', '2018-08-15 14:38:38', '123.185.159.113', '辽宁省大连市-电信ADSL', '1');
INSERT INTO `pay_loginrecord` VALUES ('40', '1', '2018-08-15 14:43:36', '114.88.77.236', '上海市-电信', '1');
INSERT INTO `pay_loginrecord` VALUES ('41', '1', '2018-08-15 15:20:31', '123.185.159.113', '辽宁省大连市-电信ADSL', '1');
INSERT INTO `pay_loginrecord` VALUES ('42', '1', '2018-08-15 15:41:11', '114.88.77.236', '上海市-电信', '1');
INSERT INTO `pay_loginrecord` VALUES ('43', '1', '2018-08-16 11:51:22', '114.88.77.236', '上海市-电信', '1');
INSERT INTO `pay_loginrecord` VALUES ('44', '1', '2018-08-21 09:10:16', '123.185.159.113', '辽宁省大连市-电信ADSL', '1');
INSERT INTO `pay_loginrecord` VALUES ('45', '1', '2018-08-21 15:02:57', '123.185.159.113', '辽宁省大连市-电信ADSL', '1');

-- ----------------------------
-- Table structure for pay_member
-- ----------------------------
DROP TABLE IF EXISTS `pay_member`;
CREATE TABLE `pay_member` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  `groupid` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户组',
  `salt` varchar(10) NOT NULL COMMENT '密码随机字符',
  `parentid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '代理ID',
  `agent_cate` int(11) NOT NULL DEFAULT '0' COMMENT '代理级别',
  `balance` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '可用余额',
  `blockedbalance` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '冻结可用余额',
  `email` varchar(100) NOT NULL,
  `activate` varchar(200) NOT NULL,
  `regdatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `activatedatetime` int(11) unsigned NOT NULL DEFAULT '0',
  `realname` varchar(50) DEFAULT NULL COMMENT '姓名',
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '性别',
  `birthday` int(11) unsigned NOT NULL DEFAULT '0',
  `sfznumber` varchar(20) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL COMMENT '联系电话',
  `qq` varchar(15) DEFAULT NULL COMMENT 'QQ',
  `address` varchar(200) DEFAULT NULL COMMENT '联系地址',
  `paypassword` varchar(32) DEFAULT NULL COMMENT '支付密码',
  `authorized` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 已认证 0 未认证 2 待审核',
  `apidomain` varchar(500) DEFAULT NULL COMMENT '授权访问域名',
  `apikey` varchar(32) NOT NULL COMMENT 'APIKEY',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1激活 0未激活',
  `receiver` varchar(255) DEFAULT NULL COMMENT '台卡显示的收款人信息',
  `unit_paying_number` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间已交易次数',
  `unit_paying_amount` decimal(11,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '单位时间已交易金额',
  `unit_frist_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间已交易的第一笔时间',
  `last_paying_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '当天最后一笔已交易时间',
  `paying_money` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '当天已交易金额',
  `login_ip` varchar(255) NOT NULL DEFAULT ' ' COMMENT '登录IP',
  `last_error_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录错误时间',
  `login_error_num` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '错误登录次数',
  `google_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启谷歌身份验证登录',
  `df_api` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启代付API',
  `df_domain` text NOT NULL COMMENT '代付域名报备',
  `df_auto_check` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代付API自动审核',
  `google_secret_key` varchar(255) NOT NULL DEFAULT '' COMMENT '谷歌密钥',
  `df_ip` text NOT NULL COMMENT '代付域名报备IP',
  `open_charge` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启充值功能',
  `session_random` varchar(50) NOT NULL DEFAULT '' COMMENT 'session随机字符串',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_member
-- ----------------------------
INSERT INTO `pay_member` VALUES ('51', 'demouser', 'fa119dbed4930d08209ba01b7650e5d4', '4', '2525', '1', '4', '0.0100', '0.0000', '1', '5694cd4db8ebb53c508e692c2b56eb97', '1534231213', '2018', 'afafa', '0', '0', '122121301212', '1', '1', '1', '96e79218965eb72c92a549dd5a330112', '1', null, '3ep5oez9eznh9x0fkxwb9hfnxf3scd9j', '1', null, '3', '0.0000', '1534814969', '1534320057', '100.0100', '', '0', '0', '0', '0', '', '0', '', '', '0', '0VM3jjpUeSYa8AaaH0OAwyjpPTCc9PUR');

-- ----------------------------
-- Table structure for pay_member_agent_cate
-- ----------------------------
DROP TABLE IF EXISTS `pay_member_agent_cate`;
CREATE TABLE `pay_member_agent_cate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cate_name` varchar(50) DEFAULT NULL COMMENT '等级名',
  `desc` varchar(255) DEFAULT NULL COMMENT '等级描述',
  `ctime` int(11) DEFAULT '0' COMMENT '添加时间',
  `sort` int(11) DEFAULT '99' COMMENT '排序',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_member_agent_cate
-- ----------------------------
INSERT INTO `pay_member_agent_cate` VALUES ('4', '普通会员', '', '1522638122', '99');
INSERT INTO `pay_member_agent_cate` VALUES ('5', '普通代理商户', '', '1522638122', '99');
INSERT INTO `pay_member_agent_cate` VALUES ('6', '中级代理商户', '', '1522638122', '99');
INSERT INTO `pay_member_agent_cate` VALUES ('7', '高级代理商户', '', '1522638122', '99');

-- ----------------------------
-- Table structure for pay_moneychange
-- ----------------------------
DROP TABLE IF EXISTS `pay_moneychange`;
CREATE TABLE `pay_moneychange` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `ymoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '原金额',
  `money` decimal(15,4) NOT NULL DEFAULT '0.0000' COMMENT '变动金额',
  `gmoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '变动后金额',
  `datetime` datetime DEFAULT NULL COMMENT '修改时间',
  `transid` varchar(50) DEFAULT NULL COMMENT '交易流水号',
  `tongdao` smallint(6) unsigned DEFAULT '0' COMMENT '支付通道ID',
  `lx` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '类型',
  `tcuserid` int(11) DEFAULT NULL,
  `tcdengji` int(11) DEFAULT NULL,
  `orderid` varchar(50) DEFAULT NULL COMMENT '订单号',
  `contentstr` varchar(255) DEFAULT NULL COMMENT '备注',
  `t` int(4) NOT NULL DEFAULT '0' COMMENT '结算方式',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_moneychange
-- ----------------------------
INSERT INTO `pay_moneychange` VALUES ('1', '51', '0.0000', '0.0100', '0.0100', '2018-08-15 15:56:19', '20180815155553575597', '903', '1', null, null, 'E20180815075548751928', 'E20180815075548751928订单充值,结算方式：t+0', '0');
INSERT INTO `pay_moneychange` VALUES ('2', '51', '0.0100', '100.0000', '100.0100', '2018-08-15 16:00:57', '20180815160022544848', '903', '1', null, null, 'E20180815075548751928', 'E20180815075548751928订单充值,结算方式：t+0', '0');
INSERT INTO `pay_moneychange` VALUES ('3', '51', '100.0100', '100.0000', '0.0100', '2018-08-15 16:01:21', 'H0815200810846041', '0', '6', null, null, 'H0815200810846041', '2018-08-15 16:01:21提现操作', '0');

-- ----------------------------
-- Table structure for pay_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_order`;
CREATE TABLE `pay_order` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pay_memberid` varchar(100) NOT NULL COMMENT '商户编号',
  `pay_orderid` varchar(100) NOT NULL COMMENT '系统订单号',
  `pay_amount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_poundage` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_actualamount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_applydate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单创建日期',
  `pay_successdate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付成功时间',
  `pay_bankcode` varchar(100) DEFAULT NULL COMMENT '银行编码',
  `pay_notifyurl` varchar(500) NOT NULL COMMENT '商家异步通知地址',
  `pay_callbackurl` varchar(500) NOT NULL COMMENT '商家页面通知地址',
  `pay_bankname` varchar(300) DEFAULT NULL,
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态: 0 未支付 1 已支付未返回 2 已支付已返回',
  `pay_productname` varchar(300) DEFAULT NULL COMMENT '商品名称',
  `pay_tongdao` varchar(50) DEFAULT NULL,
  `pay_zh_tongdao` varchar(50) DEFAULT NULL,
  `pay_tjurl` varchar(1000) DEFAULT NULL,
  `out_trade_id` varchar(50) NOT NULL COMMENT '商户订单号',
  `num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '已补发次数',
  `memberid` varchar(100) DEFAULT NULL COMMENT '支付渠道商家号',
  `key` varchar(500) DEFAULT NULL COMMENT '支付渠道密钥',
  `account` varchar(100) DEFAULT NULL COMMENT '渠道账号',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '伪删除订单 1 删除 0 未删',
  `ddlx` int(11) DEFAULT '0',
  `pay_ytongdao` varchar(50) DEFAULT NULL,
  `pay_yzh_tongdao` varchar(50) DEFAULT NULL,
  `xx` smallint(6) unsigned NOT NULL DEFAULT '0',
  `attach` text CHARACTER SET utf8mb4 COMMENT '商家附加字段,原样返回',
  `pay_channel_account` varchar(255) DEFAULT NULL COMMENT '通道账户',
  `cost` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本',
  `cost_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本费率',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子账号id',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '渠道id',
  `is_bu` int(10) DEFAULT '0' COMMENT '是否手动补单 1是',
  `bu_admin_name` varchar(255) DEFAULT NULL,
  `bu_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IND_ORD` (`pay_orderid`),
  KEY `account_id` (`account_id`),
  KEY `channel_id` (`channel_id`)
) ENGINE=MyISAM AUTO_INCREMENT=89555 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_order
-- ----------------------------

-- ----------------------------
-- Table structure for pay_paylog
-- ----------------------------
DROP TABLE IF EXISTS `pay_paylog`;
CREATE TABLE `pay_paylog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `out_trade_no` varchar(50) NOT NULL,
  `result_code` varchar(50) NOT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `fromuser` varchar(50) NOT NULL,
  `time_end` int(11) unsigned NOT NULL DEFAULT '0',
  `total_fee` smallint(6) unsigned NOT NULL DEFAULT '0',
  `payname` varchar(50) NOT NULL,
  `bank_type` varchar(20) DEFAULT NULL,
  `trade_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IND_TRD` (`transaction_id`),
  UNIQUE KEY `IND_ORD` (`out_trade_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_paylog
-- ----------------------------

-- ----------------------------
-- Table structure for pay_pay_channel_extend_fields
-- ----------------------------
DROP TABLE IF EXISTS `pay_pay_channel_extend_fields`;
CREATE TABLE `pay_pay_channel_extend_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel_id` int(11) NOT NULL DEFAULT '0' COMMENT '代付渠道ID',
  `code` varchar(64) NOT NULL DEFAULT '' COMMENT '代付渠道代码',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT '扩展字段名',
  `alias` varchar(50) NOT NULL DEFAULT '' COMMENT '扩展字段别名',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `etime` int(11) NOT NULL DEFAULT '0' COMMENT '修改时间',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '添加时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_pay_channel_extend_fields
-- ----------------------------

-- ----------------------------
-- Table structure for pay_pay_for_another
-- ----------------------------
DROP TABLE IF EXISTS `pay_pay_for_another`;
CREATE TABLE `pay_pay_for_another` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `code` varchar(64) NOT NULL COMMENT '代付代码',
  `title` varchar(64) NOT NULL COMMENT '代付名称',
  `mch_id` varchar(255) NOT NULL DEFAULT ' ' COMMENT '商户号',
  `appid` varchar(100) NOT NULL DEFAULT ' ' COMMENT '应用APPID',
  `appsecret` varchar(100) NOT NULL DEFAULT ' ' COMMENT '应用密钥',
  `signkey` varchar(500) NOT NULL DEFAULT ' ' COMMENT '加密的秘钥',
  `public_key` varchar(1000) NOT NULL DEFAULT '  ' COMMENT '加密的公钥',
  `private_key` varchar(1000) NOT NULL DEFAULT '  ' COMMENT '加密的私钥',
  `exec_gateway` varchar(255) NOT NULL DEFAULT ' ' COMMENT '请求代付的地址',
  `query_gateway` varchar(255) NOT NULL DEFAULT ' ' COMMENT '查询代付的地址',
  `serverreturn` varchar(255) NOT NULL DEFAULT ' ' COMMENT '服务器通知网址',
  `unlockdomain` varchar(255) NOT NULL DEFAULT ' ' COMMENT '防封域名',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更改时间',
  `status` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '状态 1开启 0关闭',
  `is_default` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否默认：1是，0否',
  `cost_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本费率',
  `rate_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '费率类型：按单笔收费0，按比例收费：1',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `updatetime` (`updatetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='代付通道表';

-- ----------------------------
-- Records of pay_pay_for_another
-- ----------------------------

-- ----------------------------
-- Table structure for pay_product
-- ----------------------------
DROP TABLE IF EXISTS `pay_product`;
CREATE TABLE `pay_product` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '通道名称',
  `code` varchar(50) NOT NULL COMMENT '通道代码',
  `polling` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接口模式 0 单独 1 轮询',
  `paytype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '支付类型 1 微信扫码 2 微信H5 3 支付宝扫码 4 支付宝H5 5 网银跳转 6网银直连  7 百度钱包  8 QQ钱包 9 京东钱包',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `isdisplay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '用户端显示 1 显示 0 不显示',
  `channel` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '通道ID',
  `weight` text COMMENT '平台默认通道权重',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=914 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_product
-- ----------------------------
INSERT INTO `pay_product` VALUES ('901', '微信公众号', 'WXJSAPI', '0', '2', '1', '1', '0', '');
INSERT INTO `pay_product` VALUES ('902', '微信扫码支付', 'WXSCAN', '0', '1', '1', '1', '199', '');
INSERT INTO `pay_product` VALUES ('903', '支付宝H5（支付宝使用）', 'ALISCAN', '0', '3', '1', '1', '209', '');
INSERT INTO `pay_product` VALUES ('904', '支付宝手机（非免签使用）', 'ALIWAP', '0', '4', '1', '1', '0', '');
INSERT INTO `pay_product` VALUES ('905', 'QQ手机支付', 'QQWAP', '0', '10', '1', '1', '0', '200:7');
INSERT INTO `pay_product` VALUES ('907', '网银支付', 'DBANK', '0', '5', '1', '1', '205', '');
INSERT INTO `pay_product` VALUES ('908', 'QQ扫码支付', 'QSCAN', '0', '8', '1', '1', '203', '');
INSERT INTO `pay_product` VALUES ('909', '百度钱包', 'BAIDU', '0', '7', '0', '0', '0', '');
INSERT INTO `pay_product` VALUES ('910', '京东支付', 'JDPAY', '0', '9', '1', '1', '0', '');
INSERT INTO `pay_product` VALUES ('913', '快捷支付', 'DBANK', '0', '5', '1', '1', '0', '');

-- ----------------------------
-- Table structure for pay_product_user
-- ----------------------------
DROP TABLE IF EXISTS `pay_product_user`;
CREATE TABLE `pay_product_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT ' ',
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `pid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '商户通道ID',
  `polling` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '接口模式：0 单独 1 轮询',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '通道状态 0 关闭 1 启用',
  `channel` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '指定单独通道ID',
  `weight` varchar(255) DEFAULT NULL COMMENT '通道权重',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=312 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_product_user
-- ----------------------------
INSERT INTO `pay_product_user` VALUES ('14', '2', '907', '0', '1', '0', '');
INSERT INTO `pay_product_user` VALUES ('15', '2', '901', '0', '1', '220', '');
INSERT INTO `pay_product_user` VALUES ('16', '2', '902', '0', '1', '0', '');
INSERT INTO `pay_product_user` VALUES ('17', '2', '903', '0', '1', '242', '');
INSERT INTO `pay_product_user` VALUES ('18', '2', '904', '0', '1', '208', '');
INSERT INTO `pay_product_user` VALUES ('19', '2', '905', '0', '1', '0', '');
INSERT INTO `pay_product_user` VALUES ('20', '6', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('21', '6', '902', '0', '1', '207', '');
INSERT INTO `pay_product_user` VALUES ('22', '6', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('23', '6', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('24', '6', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('25', '6', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('26', '2', '908', '0', '1', '0', '');
INSERT INTO `pay_product_user` VALUES ('27', '8', '901', '0', '0', '210', '');
INSERT INTO `pay_product_user` VALUES ('28', '8', '902', '0', '1', '218', '');
INSERT INTO `pay_product_user` VALUES ('29', '8', '903', '0', '0', '215', '');
INSERT INTO `pay_product_user` VALUES ('30', '8', '904', '0', '0', '208', '');
INSERT INTO `pay_product_user` VALUES ('31', '8', '905', '0', '0', '212', '');
INSERT INTO `pay_product_user` VALUES ('32', '8', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('33', '8', '908', '0', '0', '213', '');
INSERT INTO `pay_product_user` VALUES ('34', '9', '901', '0', '0', '210', '');
INSERT INTO `pay_product_user` VALUES ('35', '9', '902', '0', '0', '214', '');
INSERT INTO `pay_product_user` VALUES ('36', '9', '903', '0', '1', '215', '');
INSERT INTO `pay_product_user` VALUES ('37', '9', '904', '0', '0', '208', '');
INSERT INTO `pay_product_user` VALUES ('38', '9', '905', '0', '0', '212', '');
INSERT INTO `pay_product_user` VALUES ('39', '9', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('40', '9', '908', '0', '0', '213', '');
INSERT INTO `pay_product_user` VALUES ('41', '10', '901', '0', '1', '210', '');
INSERT INTO `pay_product_user` VALUES ('42', '10', '902', '0', '1', '199', '');
INSERT INTO `pay_product_user` VALUES ('43', '10', '903', '0', '1', '209', '');
INSERT INTO `pay_product_user` VALUES ('44', '10', '904', '0', '1', '208', '');
INSERT INTO `pay_product_user` VALUES ('45', '10', '905', '0', '1', '212', '');
INSERT INTO `pay_product_user` VALUES ('46', '10', '907', '0', '1', '0', '');
INSERT INTO `pay_product_user` VALUES ('47', '10', '908', '0', '1', '213', '');
INSERT INTO `pay_product_user` VALUES ('48', '11', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('49', '11', '902', '0', '1', '219', '');
INSERT INTO `pay_product_user` VALUES ('50', '11', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('51', '11', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('52', '11', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('53', '11', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('54', '11', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('55', '2', '911', '0', '1', '217', '');
INSERT INTO `pay_product_user` VALUES ('56', '12', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('57', '12', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('58', '12', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('59', '12', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('60', '12', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('61', '12', '907', '0', '1', '222', '');
INSERT INTO `pay_product_user` VALUES ('62', '12', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('63', '12', '911', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('64', '12', '913', '0', '1', '223', '');
INSERT INTO `pay_product_user` VALUES ('65', '2', '913', '0', '1', '0', '');
INSERT INTO `pay_product_user` VALUES ('66', '14', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('67', '14', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('68', '14', '903', '0', '1', '224', '');
INSERT INTO `pay_product_user` VALUES ('69', '14', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('70', '14', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('71', '14', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('72', '14', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('73', '14', '911', '0', '1', '224', '');
INSERT INTO `pay_product_user` VALUES ('74', '14', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('75', '16', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('76', '16', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('77', '16', '903', '0', '1', '224', '');
INSERT INTO `pay_product_user` VALUES ('78', '16', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('79', '16', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('80', '16', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('81', '16', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('82', '16', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('83', '13', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('84', '13', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('85', '13', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('86', '13', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('87', '13', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('88', '13', '907', '0', '1', '226', '');
INSERT INTO `pay_product_user` VALUES ('89', '13', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('90', '13', '913', '0', '1', '225', '');
INSERT INTO `pay_product_user` VALUES ('91', '17', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('92', '17', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('93', '17', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('94', '17', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('95', '17', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('96', '17', '907', '0', '0', '222', '');
INSERT INTO `pay_product_user` VALUES ('97', '17', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('98', '17', '913', '0', '0', '223', '');
INSERT INTO `pay_product_user` VALUES ('99', '19', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('100', '19', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('101', '19', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('102', '19', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('103', '19', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('104', '19', '907', '0', '1', '226', '');
INSERT INTO `pay_product_user` VALUES ('105', '19', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('106', '19', '913', '0', '1', '225', '');
INSERT INTO `pay_product_user` VALUES ('107', '21', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('108', '21', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('109', '21', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('110', '21', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('111', '21', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('112', '21', '907', '0', '1', '226', '');
INSERT INTO `pay_product_user` VALUES ('113', '21', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('114', '21', '913', '0', '1', '225', '');
INSERT INTO `pay_product_user` VALUES ('115', '22', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('116', '22', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('117', '22', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('118', '22', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('119', '22', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('120', '22', '907', '0', '1', '226', '');
INSERT INTO `pay_product_user` VALUES ('121', '22', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('122', '22', '913', '0', '1', '225', '');
INSERT INTO `pay_product_user` VALUES ('123', '23', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('124', '23', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('125', '23', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('126', '23', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('127', '23', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('128', '23', '907', '0', '1', '226', '');
INSERT INTO `pay_product_user` VALUES ('129', '23', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('130', '23', '913', '0', '1', '225', '');
INSERT INTO `pay_product_user` VALUES ('131', '25', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('132', '25', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('133', '25', '903', '0', '1', '227', '');
INSERT INTO `pay_product_user` VALUES ('134', '25', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('135', '25', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('136', '25', '907', '0', '0', '222', '');
INSERT INTO `pay_product_user` VALUES ('137', '25', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('138', '25', '913', '0', '0', '223', '');
INSERT INTO `pay_product_user` VALUES ('139', '20', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('140', '20', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('141', '20', '903', '0', '1', '224', '');
INSERT INTO `pay_product_user` VALUES ('142', '20', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('143', '20', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('144', '20', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('145', '20', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('146', '20', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('147', '7', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('148', '7', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('149', '7', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('150', '7', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('151', '7', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('152', '7', '907', '1', '1', '0', '222:1|223:1');
INSERT INTO `pay_product_user` VALUES ('153', '7', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('154', '7', '913', '1', '1', '222', '222:1|223:1');
INSERT INTO `pay_product_user` VALUES ('155', '26', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('156', '26', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('157', '26', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('158', '26', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('159', '26', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('160', '26', '907', '0', '1', '222', '');
INSERT INTO `pay_product_user` VALUES ('161', '26', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('162', '26', '913', '0', '1', '223', '');
INSERT INTO `pay_product_user` VALUES ('163', '27', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('164', '27', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('165', '27', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('166', '27', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('167', '27', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('168', '27', '907', '0', '1', '222', '');
INSERT INTO `pay_product_user` VALUES ('169', '27', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('170', '27', '913', '0', '1', '223', '');
INSERT INTO `pay_product_user` VALUES ('171', '11', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('172', '28', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('173', '28', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('174', '28', '903', '0', '1', '229', '');
INSERT INTO `pay_product_user` VALUES ('175', '28', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('176', '28', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('177', '28', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('178', '28', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('179', '28', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('180', '29', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('181', '29', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('182', '29', '903', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('183', '29', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('184', '29', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('185', '29', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('186', '29', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('187', '29', '913', '0', '1', '223', '');
INSERT INTO `pay_product_user` VALUES ('188', '31', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('189', '31', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('190', '31', '903', '0', '1', '230', '');
INSERT INTO `pay_product_user` VALUES ('191', '31', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('192', '31', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('193', '31', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('194', '31', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('195', '31', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('196', '30', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('197', '30', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('198', '30', '903', '0', '1', '232', '');
INSERT INTO `pay_product_user` VALUES ('199', '30', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('200', '30', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('201', '30', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('202', '30', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('203', '30', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('204', '32', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('205', '32', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('206', '32', '903', '0', '1', '229', '');
INSERT INTO `pay_product_user` VALUES ('207', '32', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('208', '32', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('209', '32', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('210', '32', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('211', '32', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('212', '33', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('213', '33', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('214', '33', '903', '0', '1', '231', '');
INSERT INTO `pay_product_user` VALUES ('215', '33', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('216', '33', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('217', '33', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('218', '33', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('219', '33', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('220', '34', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('221', '34', '902', '0', '1', '199', '');
INSERT INTO `pay_product_user` VALUES ('222', '34', '903', '0', '1', '232', '');
INSERT INTO `pay_product_user` VALUES ('223', '34', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('224', '34', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('225', '34', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('226', '34', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('227', '34', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('228', '35', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('229', '35', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('230', '35', '903', '0', '1', '233', '');
INSERT INTO `pay_product_user` VALUES ('231', '35', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('232', '35', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('233', '35', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('234', '35', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('235', '35', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('236', '37', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('237', '37', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('238', '37', '903', '0', '1', '235', '');
INSERT INTO `pay_product_user` VALUES ('239', '37', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('240', '37', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('241', '37', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('242', '37', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('243', '37', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('244', '40', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('245', '40', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('246', '40', '903', '0', '1', '237', '');
INSERT INTO `pay_product_user` VALUES ('247', '40', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('248', '40', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('249', '40', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('250', '40', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('251', '40', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('252', '44', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('253', '44', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('254', '44', '903', '0', '1', '238', '');
INSERT INTO `pay_product_user` VALUES ('255', '44', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('256', '44', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('257', '44', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('258', '44', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('259', '44', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('260', '43', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('261', '43', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('262', '43', '903', '0', '1', '238', '');
INSERT INTO `pay_product_user` VALUES ('263', '43', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('264', '43', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('265', '43', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('266', '43', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('267', '43', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('268', '45', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('269', '45', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('270', '45', '903', '0', '1', '239', '');
INSERT INTO `pay_product_user` VALUES ('271', '45', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('272', '45', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('273', '45', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('274', '45', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('275', '45', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('276', '46', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('277', '46', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('278', '46', '903', '0', '1', '241', '');
INSERT INTO `pay_product_user` VALUES ('279', '46', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('280', '46', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('281', '46', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('282', '46', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('283', '46', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('284', '47', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('285', '47', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('286', '47', '903', '0', '1', '242', '');
INSERT INTO `pay_product_user` VALUES ('287', '47', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('288', '47', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('289', '47', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('290', '47', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('291', '47', '910', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('292', '47', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('293', '2', '910', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('294', '54', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('295', '54', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('296', '54', '903', '1', '1', '0', '242:');
INSERT INTO `pay_product_user` VALUES ('297', '54', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('298', '54', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('299', '54', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('300', '54', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('301', '54', '910', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('302', '54', '913', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('303', '51', '901', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('304', '51', '902', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('305', '51', '903', '0', '1', '236', '');
INSERT INTO `pay_product_user` VALUES ('306', '51', '904', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('307', '51', '905', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('308', '51', '907', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('309', '51', '908', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('310', '51', '910', '0', '0', '0', '');
INSERT INTO `pay_product_user` VALUES ('311', '51', '913', '0', '0', '0', '');

-- ----------------------------
-- Table structure for pay_reconciliation
-- ----------------------------
DROP TABLE IF EXISTS `pay_reconciliation`;
CREATE TABLE `pay_reconciliation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT '0' COMMENT '用户ID',
  `order_total_count` int(11) DEFAULT '0' COMMENT '总订单数',
  `order_success_count` int(11) DEFAULT '0' COMMENT '成功订单数',
  `order_fail_count` int(11) DEFAULT '0' COMMENT '未支付订单数',
  `order_total_amount` decimal(15,4) DEFAULT '0.0000' COMMENT '订单总额',
  `order_success_amount` decimal(15,4) DEFAULT '0.0000' COMMENT '订单实付总额',
  `date` date DEFAULT NULL COMMENT '日期',
  `ctime` int(11) DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_reconciliation
-- ----------------------------
INSERT INTO `pay_reconciliation` VALUES ('1', '47', '0', '0', '0', null, null, '2018-08-13', '1534130694');
INSERT INTO `pay_reconciliation` VALUES ('2', '47', '0', '0', '0', null, null, '2018-08-12', '1534130694');
INSERT INTO `pay_reconciliation` VALUES ('3', '47', '0', '0', '0', null, null, '2018-08-11', '1534130694');
INSERT INTO `pay_reconciliation` VALUES ('4', null, '0', '0', '0', null, null, '2018-08-13', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('5', null, '0', '0', '0', null, null, '2018-08-12', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('6', null, '0', '0', '0', null, null, '2018-08-11', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('7', null, '0', '0', '0', null, null, '2018-08-10', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('8', null, '0', '0', '0', null, null, '2018-08-09', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('9', null, '0', '0', '0', null, null, '2018-08-08', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('10', null, '0', '0', '0', null, null, '2018-08-07', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('11', null, '0', '0', '0', null, null, '2018-08-06', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('12', null, '0', '0', '0', null, null, '2018-08-05', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('13', null, '0', '0', '0', null, null, '2018-08-04', '1534130800');
INSERT INTO `pay_reconciliation` VALUES ('14', '51', '7', '1', '6', '4.0300', '0.0100', '2018-08-15', '1534319695');
INSERT INTO `pay_reconciliation` VALUES ('15', '51', '0', '0', '0', null, null, '2018-08-14', '1534319695');

-- ----------------------------
-- Table structure for pay_redo_order
-- ----------------------------
DROP TABLE IF EXISTS `pay_redo_order`;
CREATE TABLE `pay_redo_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL DEFAULT '0' COMMENT '操作管理员',
  `money` decimal(15,4) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1：增加 2：减少',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '冲正备注',
  `date` datetime NOT NULL COMMENT '冲正周期',
  `ctime` int(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_redo_order
-- ----------------------------

-- ----------------------------
-- Table structure for pay_route
-- ----------------------------
DROP TABLE IF EXISTS `pay_route`;
CREATE TABLE `pay_route` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `urlstr` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_route
-- ----------------------------

-- ----------------------------
-- Table structure for pay_sms
-- ----------------------------
DROP TABLE IF EXISTS `pay_sms`;
CREATE TABLE `pay_sms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_key` varchar(255) DEFAULT NULL COMMENT 'App Key',
  `app_secret` varchar(255) DEFAULT NULL COMMENT 'App Secret',
  `sign_name` varchar(255) DEFAULT NULL COMMENT '默认签名',
  `is_open` int(11) DEFAULT '0' COMMENT '是否开启，0关闭，1开启',
  `admin_mobile` varchar(255) DEFAULT NULL COMMENT '管理员接收手机',
  `is_receive` int(11) DEFAULT '0' COMMENT '是否开启，0关闭，1开启',
  `sms_channel` varchar(20) NOT NULL DEFAULT 'aliyun' COMMENT '短信通道',
  `smsbao_user` varchar(50) NOT NULL DEFAULT '' COMMENT '短信宝账号',
  `smsbao_pass` varchar(50) NOT NULL DEFAULT '' COMMENT '短信宝密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_sms
-- ----------------------------

-- ----------------------------
-- Table structure for pay_sms_template
-- ----------------------------
DROP TABLE IF EXISTS `pay_sms_template`;
CREATE TABLE `pay_sms_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `template_code` varchar(255) DEFAULT NULL COMMENT '模板代码',
  `call_index` varchar(255) DEFAULT NULL COMMENT '调用字符串',
  `template_content` text COMMENT '模板内容',
  `ctime` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_sms_template
-- ----------------------------
INSERT INTO `pay_sms_template` VALUES ('3', '修改支付密码', 'SMS_111795375', 'editPayPassword', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1512202260');
INSERT INTO `pay_sms_template` VALUES ('4', '修改登录密码', 'SMS_111795375', 'editPassword', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1512190115');
INSERT INTO `pay_sms_template` VALUES ('5', '异地登录', 'SMS_111795375', 'loginWarning', '您的账号于${time}登录异常，异常登录地址：${address}，如非本人操纵，请及时修改账号密码。', '1512202260');
INSERT INTO `pay_sms_template` VALUES ('6', '申请结算', 'SMS_111795375', 'clearing', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1512202260');
INSERT INTO `pay_sms_template` VALUES ('7', '委托结算', 'SMS_111795375', 'entrusted', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1512202260');
INSERT INTO `pay_sms_template` VALUES ('8', '绑定手机', 'SMS_119087905', 'bindMobile', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1514534290');
INSERT INTO `pay_sms_template` VALUES ('9', '更新手机', 'SMS_119087905', 'editMobile', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1514535688');
INSERT INTO `pay_sms_template` VALUES ('10', '更新银行卡 ', 'SMS_119087905 ', 'addBankcardSend', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1514535688');
INSERT INTO `pay_sms_template` VALUES ('11', '修改个人资料', 'SMS_111795375', 'saveProfile', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', null);
INSERT INTO `pay_sms_template` VALUES ('12', '绑定管理员手机号码', 'SMS_111795375', 'adminbindMobile', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('13', '修改管理员手机号码', 'SMS_111795375', 'admineditMobile', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('14', '批量删除订单', 'SMS_111795375', 'delOrderSend', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('15', '解绑谷歌身份验证器', 'SMS_111795375', 'unbindGoogle', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('63', '增加/减少余额（冲正）', 'SMS_111795375', 'adjustUserMoneySend', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('64', '提交代付', 'SMS_111795375', 'submitDfSend', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('19', '解绑谷歌身份验证器', 'SMS_111795375', 'unbindGoogle', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('23', '设置订单为已支付', 'SMS_111795375', 'setOrderPaidSend', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');
INSERT INTO `pay_sms_template` VALUES ('24', '清理数据', 'SMS_111795375', 'clearDataSend', '您的验证码为：${code} ，你正在进行${opration}操作，该验证码 5 分钟内有效，请勿泄露于他人。', '1527670734');

-- ----------------------------
-- Table structure for pay_systembank
-- ----------------------------
DROP TABLE IF EXISTS `pay_systembank`;
CREATE TABLE `pay_systembank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bankcode` varchar(100) DEFAULT NULL,
  `bankname` varchar(300) DEFAULT NULL,
  `images` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=195 DEFAULT CHARSET=utf8 COMMENT='结算银行';

-- ----------------------------
-- Records of pay_systembank
-- ----------------------------
INSERT INTO `pay_systembank` VALUES ('162', 'BOB', '北京银行', 'BOB.gif');
INSERT INTO `pay_systembank` VALUES ('164', 'BEA', '东亚银行', 'BEA.gif');
INSERT INTO `pay_systembank` VALUES ('165', 'ICBC', '中国工商银行', 'ICBC.gif');
INSERT INTO `pay_systembank` VALUES ('166', 'CEB', '中国光大银行', 'CEB.gif');
INSERT INTO `pay_systembank` VALUES ('167', 'GDB', '广发银行', 'GDB.gif');
INSERT INTO `pay_systembank` VALUES ('168', 'HXB', '华夏银行', 'HXB.gif');
INSERT INTO `pay_systembank` VALUES ('169', 'CCB', '中国建设银行', 'CCB.gif');
INSERT INTO `pay_systembank` VALUES ('170', 'BCM', '交通银行', 'BCM.gif');
INSERT INTO `pay_systembank` VALUES ('171', 'CMSB', '中国民生银行', 'CMSB.gif');
INSERT INTO `pay_systembank` VALUES ('172', 'NJCB', '南京银行', 'NJCB.gif');
INSERT INTO `pay_systembank` VALUES ('173', 'NBCB', '宁波银行', 'NBCB.gif');
INSERT INTO `pay_systembank` VALUES ('174', 'ABC', '中国农业银行', '5414c87492ad8.gif');
INSERT INTO `pay_systembank` VALUES ('175', 'PAB', '平安银行', '5414c0929a632.gif');
INSERT INTO `pay_systembank` VALUES ('176', 'BOS', '上海银行', 'BOS.gif');
INSERT INTO `pay_systembank` VALUES ('177', 'SPDB', '上海浦东发展银行', 'SPDB.gif');
INSERT INTO `pay_systembank` VALUES ('178', 'SDB', '深圳发展银行', 'SDB.gif');
INSERT INTO `pay_systembank` VALUES ('179', 'CIB', '兴业银行', 'CIB.gif');
INSERT INTO `pay_systembank` VALUES ('180', 'PSBC', '中国邮政储蓄银行', 'PSBC.gif');
INSERT INTO `pay_systembank` VALUES ('181', 'CMBC', '招商银行', 'CMBC.gif');
INSERT INTO `pay_systembank` VALUES ('182', 'CZB', '浙商银行', 'CZB.gif');
INSERT INTO `pay_systembank` VALUES ('183', 'BOC', '中国银行', 'BOC.gif');
INSERT INTO `pay_systembank` VALUES ('184', 'CNCB', '中信银行', 'CNCB.gif');
INSERT INTO `pay_systembank` VALUES ('193', 'ALIPAY', '支付宝', '58b83a5820644.jpg');
INSERT INTO `pay_systembank` VALUES ('194', 'WXZF', '微信支付', '58b83a757a298.jpg');

-- ----------------------------
-- Table structure for pay_tikuanconfig
-- ----------------------------
DROP TABLE IF EXISTS `pay_tikuanconfig`;
CREATE TABLE `pay_tikuanconfig` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '商户编号',
  `tkzxmoney` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最小提款金额',
  `tkzdmoney` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最大提款金额',
  `dayzdmoney` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当日提款最大总金额',
  `dayzdnum` int(11) NOT NULL DEFAULT '0' COMMENT '当日提款最大次数',
  `t1zt` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT 'T+1 ：1开启 0 关闭',
  `t0zt` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'T+0 ：1开启 0 关闭',
  `gmt0` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '购买T0',
  `tkzt` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '提款设置 1 开启 0 关闭',
  `tktype` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '提款手续费类型 1 每笔 0 比例 ',
  `systemxz` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 系统规则 1 用户规则',
  `sxfrate` varchar(20) DEFAULT NULL COMMENT '单笔提款比例',
  `sxffixed` varchar(20) DEFAULT NULL COMMENT '单笔提款手续费',
  `issystem` tinyint(1) unsigned DEFAULT '0' COMMENT '平台规则 1 是 0 否',
  `allowstart` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '提款允许开始时间',
  `allowend` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '提款允许结束时间',
  `daycardzdmoney` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单人单卡单日最高提现额',
  `auto_df_switch` tinyint(1) NOT NULL DEFAULT '0' COMMENT '自动代付开关',
  `auto_df_maxmoney` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单笔代付最大金额限制',
  `auto_df_stime` varchar(20) NOT NULL DEFAULT '' COMMENT '自动代付开始时间',
  `auto_df_etime` varchar(20) NOT NULL DEFAULT '' COMMENT '自动代付结束时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `IND_UID` (`userid`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_tikuanconfig
-- ----------------------------
INSERT INTO `pay_tikuanconfig` VALUES ('28', '1', '100.00', '500000.00', '5000000.00', '50', '0', '0', '200.00', '1', '1', '0', '2', '0', '1', '9', '0', '0.00', '0', '0.00', '', '');

-- ----------------------------
-- Table structure for pay_tikuanholiday
-- ----------------------------
DROP TABLE IF EXISTS `pay_tikuanholiday`;
CREATE TABLE `pay_tikuanholiday` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '排除日期',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='排除节假日';

-- ----------------------------
-- Records of pay_tikuanholiday
-- ----------------------------
INSERT INTO `pay_tikuanholiday` VALUES ('5', '1503676800');
INSERT INTO `pay_tikuanholiday` VALUES ('6', '1503763200');
INSERT INTO `pay_tikuanholiday` VALUES ('8', '1504281600');
INSERT INTO `pay_tikuanholiday` VALUES ('9', '1504368000');

-- ----------------------------
-- Table structure for pay_tikuanmoney
-- ----------------------------
DROP TABLE IF EXISTS `pay_tikuanmoney`;
CREATE TABLE `pay_tikuanmoney` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0' COMMENT '结算用户ID',
  `websiteid` int(11) NOT NULL DEFAULT '0',
  `payapiid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '结算通道ID',
  `t` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '结算方式: 1 T+1 ,0 T+0',
  `money` decimal(10,2) unsigned NOT NULL DEFAULT '0.00',
  `datetype` varchar(2) NOT NULL,
  `createtime` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_tikuanmoney
-- ----------------------------

-- ----------------------------
-- Table structure for pay_tikuantime
-- ----------------------------
DROP TABLE IF EXISTS `pay_tikuantime`;
CREATE TABLE `pay_tikuantime` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `baiks` tinyint(2) unsigned DEFAULT '0' COMMENT '白天提款开始时间',
  `baijs` tinyint(2) unsigned DEFAULT '0' COMMENT '白天提款结束时间',
  `wanks` tinyint(2) unsigned DEFAULT '0' COMMENT '晚间提款开始时间',
  `wanjs` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='提款时间';

-- ----------------------------
-- Records of pay_tikuantime
-- ----------------------------
INSERT INTO `pay_tikuantime` VALUES ('1', '24', '17', '18', '24');

-- ----------------------------
-- Table structure for pay_tklist
-- ----------------------------
DROP TABLE IF EXISTS `pay_tklist`;
CREATE TABLE `pay_tklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `bankname` varchar(300) NOT NULL,
  `bankzhiname` varchar(300) NOT NULL,
  `banknumber` varchar(300) NOT NULL,
  `bankfullname` varchar(300) NOT NULL,
  `sheng` varchar(300) NOT NULL,
  `shi` varchar(300) NOT NULL,
  `sqdatetime` datetime DEFAULT NULL,
  `cldatetime` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `tkmoney` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `sxfmoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `money` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `t` int(4) NOT NULL DEFAULT '1',
  `payapiid` int(11) NOT NULL DEFAULT '0',
  `memo` text COMMENT '备注',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_tklist
-- ----------------------------
INSERT INTO `pay_tklist` VALUES ('1', '51', '东亚银行', '阿斯达', '23423423', '阿斯顿', '阿萨德', '位', '2018-08-15 16:01:21', '2018-08-15 16:02:06', '2', '100.0000', '0.0000', '100.0000', '0', '0', null);

-- ----------------------------
-- Table structure for pay_updatelog
-- ----------------------------
DROP TABLE IF EXISTS `pay_updatelog`;
CREATE TABLE `pay_updatelog` (
  `version` varchar(20) NOT NULL,
  `lastupdate` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_updatelog
-- ----------------------------

-- ----------------------------
-- Table structure for pay_userrate
-- ----------------------------
DROP TABLE IF EXISTS `pay_userrate`;
CREATE TABLE `pay_userrate` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `payapiid` smallint(6) unsigned NOT NULL DEFAULT '0' COMMENT '通道ID',
  `feilv` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '运营费率',
  `fengding` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '封顶费率',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=163 DEFAULT CHARSET=utf8 COMMENT='商户通道费率';

-- ----------------------------
-- Records of pay_userrate
-- ----------------------------
INSERT INTO `pay_userrate` VALUES ('154', '50', '901', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('155', '50', '902', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('156', '50', '903', '0.0400', '0.0000');
INSERT INTO `pay_userrate` VALUES ('157', '50', '904', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('158', '50', '905', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('159', '50', '907', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('160', '50', '908', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('161', '50', '910', '0.0000', '0.0000');
INSERT INTO `pay_userrate` VALUES ('162', '50', '913', '0.0000', '0.0000');

-- ----------------------------
-- Table structure for pay_user_code
-- ----------------------------
DROP TABLE IF EXISTS `pay_user_code`;
CREATE TABLE `pay_user_code` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) DEFAULT '0' COMMENT '0找回密码',
  `code` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `status` int(11) DEFAULT '0',
  `ctime` int(11) DEFAULT NULL,
  `uptime` int(11) DEFAULT NULL COMMENT '更新时间',
  `endtime` int(11) DEFAULT NULL COMMENT '有效时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_user_code
-- ----------------------------

-- ----------------------------
-- Table structure for pay_user_riskcontrol_config
-- ----------------------------
DROP TABLE IF EXISTS `pay_user_riskcontrol_config`;
CREATE TABLE `pay_user_riskcontrol_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID',
  `min_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最小金额',
  `max_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单笔最大金额',
  `unit_all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '单位时间内交易总金额',
  `all_money` decimal(11,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '当天交易总金额',
  `start_time` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '一天交易开始时间',
  `end_time` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '一天交易结束时间',
  `unit_number` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间内交易的总笔数',
  `is_system` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否平台规则',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态:1开通，0关闭',
  `add_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `edit_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '修改时间',
  `time_unit` char(1) NOT NULL DEFAULT 'i' COMMENT '限制的时间单位',
  `unit_interval` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单位时间值',
  `domain` varchar(255) NOT NULL DEFAULT ' ' COMMENT '防封域名',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COMMENT='交易配置';

-- ----------------------------
-- Records of pay_user_riskcontrol_config
-- ----------------------------
INSERT INTO `pay_user_riskcontrol_config` VALUES ('1', '0', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '1', '0', '1522289886', '0', 'i', '0', '');
INSERT INTO `pay_user_riskcontrol_config` VALUES ('2', '2', '10.00', '100.00', '200.00', '10000.00', '0', '0', '5', '0', '0', '1530668981', '1522946735', 'i', '1', 'zhiyu.tianniu.cc');
INSERT INTO `pay_user_riskcontrol_config` VALUES ('3', '8', '0.00', '10000.00', '0.00', '0.00', '0', '0', '0', '0', '1', '1524718852', '1524718845', 's', '0', '');
INSERT INTO `pay_user_riskcontrol_config` VALUES ('4', '40', '0.00', '0.00', '0.00', '0.00', '0', '0', '0', '0', '0', '1529990077', '1529988926', 's', '0', '');
INSERT INTO `pay_user_riskcontrol_config` VALUES ('5', '45', '100.00', '3000.00', '0.00', '1000000.00', '0', '0', '0', '0', '0', '1530669485', '1530607791', 'i', '1', '');
INSERT INTO `pay_user_riskcontrol_config` VALUES ('6', '51', '1.00', '100.00', '2.00', '0.00', '9', '10', '2', '0', '1', '1534814580', '1534813887', 'i', '5', '');

-- ----------------------------
-- Table structure for pay_version
-- ----------------------------
DROP TABLE IF EXISTS `pay_version`;
CREATE TABLE `pay_version` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) NOT NULL DEFAULT '0' COMMENT '版本',
  `author` varchar(11) NOT NULL DEFAULT ' ' COMMENT '作者',
  `save_time` varchar(255) NOT NULL DEFAULT '0000-00-00' COMMENT '修改时间,格式YYYY-mm-dd',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='数据库版本表';

-- ----------------------------
-- Records of pay_version
-- ----------------------------
INSERT INTO `pay_version` VALUES ('1', '5.5', '陈嘉杰', '2018-4-8');
INSERT INTO `pay_version` VALUES ('2', '5.7', ' mio', '2018-4-17');
INSERT INTO `pay_version` VALUES ('3', '5.6', ' mapeijian', '2018/4/17 17:45:33');

-- ----------------------------
-- Table structure for pay_websiteconfig
-- ----------------------------
DROP TABLE IF EXISTS `pay_websiteconfig`;
CREATE TABLE `pay_websiteconfig` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `websitename` varchar(300) DEFAULT NULL COMMENT '网站名称',
  `domain` varchar(300) DEFAULT NULL COMMENT '网址',
  `email` varchar(100) DEFAULT NULL,
  `tel` varchar(30) DEFAULT NULL,
  `qq` varchar(30) DEFAULT NULL,
  `directory` varchar(100) DEFAULT NULL COMMENT '后台目录名称',
  `icp` varchar(100) DEFAULT NULL,
  `tongji` varchar(1000) DEFAULT NULL COMMENT '统计',
  `login` varchar(100) DEFAULT NULL COMMENT '登录地址',
  `payingservice` tinyint(1) unsigned DEFAULT '0' COMMENT '商户代付 1 开启 0 关闭',
  `authorized` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '商户认证 1 开启 0 关闭',
  `invitecode` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '邀请码注册',
  `company` varchar(200) DEFAULT NULL COMMENT '公司名称',
  `serverkey` varchar(50) DEFAULT NULL COMMENT '授权服务key',
  `withdraw` tinyint(1) DEFAULT '0' COMMENT '提现通知：0关闭，1开启',
  `login_warning_num` tinyint(3) unsigned NOT NULL DEFAULT '3' COMMENT '前台可以错误登录次数',
  `login_ip` varchar(1000) NOT NULL DEFAULT ' ' COMMENT '登录IP',
  `is_repeat_order` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '是否允许重复订单:1是，0否',
  `google_auth` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启谷歌身份验证登录',
  `df_api` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启代付API',
  `register_need_activate` tinyint(1) NOT NULL DEFAULT '0' COMMENT '用户注册是否需激活',
  `random_mchno` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否开启随机商户号',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_websiteconfig
-- ----------------------------
INSERT INTO `pay_websiteconfig` VALUES ('1', '快入宝支付', 'j.ty480.top', '111@qq.com', '09167658998', '111', 'manage', '', '11', 'zhiyu', '1', '1', '1', '快入宝支付', '0d6de302cbc615de3b09463acea87662', '0', '30', ' ', '1', '0', '0', '0', '0');

-- ----------------------------
-- Table structure for pay_wttklist
-- ----------------------------
DROP TABLE IF EXISTS `pay_wttklist`;
CREATE TABLE `pay_wttklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `bankname` varchar(300) NOT NULL,
  `bankzhiname` varchar(300) NOT NULL,
  `banknumber` varchar(300) NOT NULL,
  `bankfullname` varchar(300) NOT NULL,
  `sheng` varchar(300) NOT NULL,
  `shi` varchar(300) NOT NULL,
  `sqdatetime` datetime DEFAULT NULL,
  `cldatetime` datetime DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `tkmoney` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `sxfmoney` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '手续费',
  `money` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '实际到账',
  `t` int(4) NOT NULL DEFAULT '1',
  `payapiid` int(11) NOT NULL DEFAULT '0',
  `memo` text COMMENT '备注',
  `additional` varchar(1000) NOT NULL DEFAULT ' ' COMMENT '额外的参数',
  `code` varchar(64) NOT NULL DEFAULT ' ' COMMENT '代码控制器名称',
  `df_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '代付通道id',
  `df_name` varchar(64) NOT NULL DEFAULT ' ' COMMENT '代付名称',
  `orderid` varchar(100) NOT NULL DEFAULT ' ' COMMENT '订单id',
  `cost` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '成本',
  `cost_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本费率',
  `rate_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '费率类型：按单笔收费0，按比例收费：1',
  `extends` text COMMENT '扩展数据',
  `out_trade_no` varchar(30) DEFAULT '' COMMENT '下游订单号',
  `df_api_id` int(11) DEFAULT '0' COMMENT '代付API申请ID',
  `auto_submit_try` int(10) NOT NULL DEFAULT '0' COMMENT '自动代付尝试提交次数',
  `is_auto` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否自动提交',
  `last_submit_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后提交时间',
  `df_lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '代付锁，防止重复提交',
  `auto_query_num` int(10) NOT NULL DEFAULT '0' COMMENT '自动查询次数',
  `channel_mch_id` varchar(50) NOT NULL DEFAULT '' COMMENT '通道商户号',
  PRIMARY KEY (`id`),
  KEY `code` (`code`),
  KEY `df_id` (`df_id`),
  KEY `orderid` (`orderid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay_wttklist
-- ----------------------------
