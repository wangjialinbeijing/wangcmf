-- Adminer 4.3.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `db_auth_group`;
CREATE TABLE `db_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `db_auth_group_access`;
CREATE TABLE `db_auth_group_access` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`user_id`,`group_id`),
  KEY `uid` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `db_auth_rule`;
CREATE TABLE `db_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


SET NAMES utf8mb4;

DROP TABLE IF EXISTS `db_config`;
CREATE TABLE `db_config` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` longtext NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `create_time` int(10) NOT NULL DEFAULT '0',
  `update_time` int(10) NOT NULL DEFAULT '0',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0：字符串1：数组',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `db_config` (`id`, `name`, `value`, `status`, `create_time`, `update_time`, `type`) VALUES
(1,	'sites_name',	'超级管理员后台',	1,	0,	0,	0);

DROP TABLE IF EXISTS `db_menu`;
CREATE TABLE `db_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '文档ID',
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`id`),
  KEY `pid` (`pid`),
  KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `db_menu` (`id`, `title`, `pid`, `sort`, `url`, `hide`, `tip`, `group`, `is_dev`, `status`) VALUES
(3,	'文档列表',	2,	0,	'article/index',	1,	'',	'内容',	0,	1),
(2,	'内容',	0,	2,	'Article/index',	0,	'',	'',	0,	1),
(1,	'首页',	0,	1,	'Index/index',	0,	'',	'',	0,	1),
(4,	'新增',	3,	0,	'article/add',	0,	'',	'',	0,	1),
(5,	'编辑',	3,	0,	'article/edit',	0,	'',	'',	0,	1),
(6,	'改变状态',	3,	0,	'article/setStatus',	0,	'',	'',	0,	1),
(7,	'保存',	3,	0,	'article/update',	0,	'',	'',	0,	1),
(8,	'保存草稿',	3,	0,	'article/autoSave',	0,	'',	'',	0,	1),
(9,	'移动',	3,	0,	'article/move',	0,	'',	'',	0,	1),
(10,	'复制',	3,	0,	'article/copy',	0,	'',	'',	0,	1),
(11,	'粘贴',	3,	0,	'article/paste',	0,	'',	'',	0,	1),
(12,	'导入',	3,	0,	'article/batchOperate',	0,	'',	'',	0,	1),
(13,	'回收站',	2,	0,	'article/recycle',	1,	'',	'内容',	0,	1),
(14,	'还原',	13,	0,	'article/permit',	0,	'',	'',	0,	1),
(15,	'清空',	13,	0,	'article/clear',	0,	'',	'',	0,	1),
(16,	'用户',	0,	3,	'User/index',	0,	'',	'',	0,	1),
(17,	'用户信息',	16,	0,	'User/index',	0,	'',	'用户管理',	0,	1),
(18,	'新增用户',	17,	0,	'User/add',	0,	'添加新用户',	'',	0,	1),
(19,	'用户行为',	16,	0,	'User/action',	0,	'',	'行为管理',	0,	1),
(20,	'新增用户行为',	19,	0,	'User/addaction',	0,	'',	'',	0,	1),
(21,	'编辑用户行为',	19,	0,	'User/editaction',	0,	'',	'',	0,	1),
(22,	'保存用户行为',	19,	0,	'User/saveAction',	0,	'\"用户->用户行为\"保存编辑和新增的用户行为',	'',	0,	1),
(23,	'变更行为状态',	19,	0,	'User/setStatus',	0,	'\"用户->用户行为\"中的启用,禁用和删除权限',	'',	0,	1),
(24,	'禁用会员',	19,	0,	'User/changeStatus?method=forbidUser',	0,	'\"用户->用户信息\"中的禁用',	'',	0,	1),
(25,	'启用会员',	19,	0,	'User/changeStatus?method=resumeUser',	0,	'\"用户->用户信息\"中的启用',	'',	0,	1),
(26,	'删除会员',	19,	0,	'User/changeStatus?method=deleteUser',	0,	'\"用户->用户信息\"中的删除',	'',	0,	1),
(27,	'权限管理',	16,	0,	'AuthManager/index',	0,	'',	'用户管理',	0,	1),
(28,	'删除',	27,	0,	'AuthManager/changeStatus?method=deleteGroup',	0,	'删除用户组',	'',	0,	1),
(29,	'禁用',	27,	0,	'AuthManager/changeStatus?method=forbidGroup',	0,	'禁用用户组',	'',	0,	1),
(30,	'恢复',	27,	0,	'AuthManager/changeStatus?method=resumeGroup',	0,	'恢复已禁用的用户组',	'',	0,	1),
(31,	'新增',	27,	0,	'AuthManager/createGroup',	0,	'创建新的用户组',	'',	0,	1),
(32,	'编辑',	27,	0,	'AuthManager/editGroup',	0,	'编辑用户组名称和描述',	'',	0,	1),
(33,	'保存用户组',	27,	0,	'AuthManager/writeGroup',	0,	'新增和编辑用户组的\"保存\"按钮',	'',	0,	1),
(34,	'授权',	27,	0,	'AuthManager/group',	0,	'\"后台 \\ 用户 \\ 用户信息\"列表页的\"授权\"操作按钮,用于设置用户所属用户组',	'',	0,	1),
(35,	'访问授权',	27,	0,	'AuthManager/access',	0,	'\"后台 \\ 用户 \\ 权限管理\"列表页的\"访问授权\"操作按钮',	'',	0,	1),
(36,	'成员授权',	27,	0,	'AuthManager/user',	0,	'\"后台 \\ 用户 \\ 权限管理\"列表页的\"成员授权\"操作按钮',	'',	0,	1),
(37,	'解除授权',	27,	0,	'AuthManager/removeFromGroup',	0,	'\"成员授权\"列表页内的解除授权操作按钮',	'',	0,	1),
(38,	'保存成员授权',	27,	0,	'AuthManager/addToGroup',	0,	'\"用户信息\"列表页\"授权\"时的\"保存\"按钮和\"成员授权\"里右上角的\"添加\"按钮)',	'',	0,	1),
(39,	'分类授权',	27,	0,	'AuthManager/category',	0,	'\"后台 \\ 用户 \\ 权限管理\"列表页的\"分类授权\"操作按钮',	'',	0,	1),
(40,	'保存分类授权',	27,	0,	'AuthManager/addToCategory',	0,	'\"分类授权\"页面的\"保存\"按钮',	'',	0,	1),
(41,	'模型授权',	27,	0,	'AuthManager/modelauth',	0,	'\"后台 \\ 用户 \\ 权限管理\"列表页的\"模型授权\"操作按钮',	'',	0,	1),
(42,	'保存模型授权',	27,	0,	'AuthManager/addToModel',	0,	'\"分类授权\"页面的\"保存\"按钮',	'',	0,	1),
(43,	'扩展',	0,	7,	'Addons/index',	0,	'',	'',	0,	1),
(44,	'插件管理',	43,	1,	'Addons/index',	0,	'',	'扩展',	0,	1),
(45,	'创建',	44,	0,	'Addons/create',	0,	'服务器上创建插件结构向导',	'',	0,	1),
(46,	'检测创建',	44,	0,	'Addons/checkForm',	0,	'检测插件是否可以创建',	'',	0,	1),
(47,	'预览',	44,	0,	'Addons/preview',	0,	'预览插件定义类文件',	'',	0,	1),
(48,	'快速生成插件',	44,	0,	'Addons/build',	0,	'开始生成插件结构',	'',	0,	1),
(49,	'设置',	44,	0,	'Addons/config',	0,	'设置插件配置',	'',	0,	1),
(50,	'禁用',	44,	0,	'Addons/disable',	0,	'禁用插件',	'',	0,	1),
(51,	'启用',	44,	0,	'Addons/enable',	0,	'启用插件',	'',	0,	1),
(52,	'安装',	44,	0,	'Addons/install',	0,	'安装插件',	'',	0,	1),
(53,	'卸载',	44,	0,	'Addons/uninstall',	0,	'卸载插件',	'',	0,	1),
(54,	'更新配置',	44,	0,	'Addons/saveconfig',	0,	'更新插件配置处理',	'',	0,	1),
(55,	'插件后台列表',	44,	0,	'Addons/adminList',	0,	'',	'',	0,	1),
(56,	'URL方式访问插件',	44,	0,	'Addons/execute',	0,	'控制是否有权限通过url访问插件控制器方法',	'',	0,	1),
(57,	'钩子管理',	43,	2,	'Addons/hooks',	0,	'',	'扩展',	0,	1),
(58,	'模型管理',	68,	3,	'Model/index',	0,	'',	'系统设置',	0,	1),
(59,	'新增',	58,	0,	'model/add',	0,	'',	'',	0,	1),
(60,	'编辑',	58,	0,	'model/edit',	0,	'',	'',	0,	1),
(61,	'改变状态',	58,	0,	'model/setStatus',	0,	'',	'',	0,	1),
(62,	'保存数据',	58,	0,	'model/update',	0,	'',	'',	0,	1),
(63,	'属性管理',	68,	0,	'Attribute/index',	1,	'网站属性配置。',	'',	0,	1),
(64,	'新增',	63,	0,	'Attribute/add',	0,	'',	'',	0,	1),
(65,	'编辑',	63,	0,	'Attribute/edit',	0,	'',	'',	0,	1),
(66,	'改变状态',	63,	0,	'Attribute/setStatus',	0,	'',	'',	0,	1),
(67,	'保存数据',	63,	0,	'Attribute/update',	0,	'',	'',	0,	1),
(68,	'系统',	0,	4,	'Config/group',	0,	'',	'',	0,	1),
(69,	'网站设置',	68,	1,	'Config/group',	0,	'',	'系统设置',	0,	1),
(70,	'配置管理',	68,	4,	'Config/index',	0,	'',	'系统设置',	0,	1),
(71,	'编辑',	70,	0,	'Config/edit',	0,	'新增编辑和保存配置',	'',	0,	1),
(72,	'删除',	70,	0,	'Config/del',	0,	'删除配置',	'',	0,	1),
(73,	'新增',	70,	0,	'Config/add',	0,	'新增配置',	'',	0,	1),
(74,	'保存',	70,	0,	'Config/save',	0,	'保存配置',	'',	0,	1),
(75,	'菜单管理',	68,	5,	'Menu/index',	0,	'',	'系统设置',	0,	1),
(76,	'导航管理',	68,	6,	'Channel/index',	0,	'',	'系统设置',	0,	1),
(77,	'新增',	76,	0,	'Channel/add',	0,	'',	'',	0,	1),
(78,	'编辑',	76,	0,	'Channel/edit',	0,	'',	'',	0,	1),
(79,	'删除',	76,	0,	'Channel/del',	0,	'',	'',	0,	1),
(80,	'分类管理',	68,	2,	'Category/index',	0,	'',	'系统设置',	0,	1),
(81,	'编辑',	80,	0,	'Category/edit',	0,	'编辑和保存栏目分类',	'',	0,	1),
(82,	'新增',	80,	0,	'Category/add',	0,	'新增栏目分类',	'',	0,	1),
(83,	'删除',	80,	0,	'Category/remove',	0,	'删除栏目分类',	'',	0,	1),
(84,	'移动',	80,	0,	'Category/operate/type/move',	0,	'移动栏目分类',	'',	0,	1),
(85,	'合并',	80,	0,	'Category/operate/type/merge',	0,	'合并栏目分类',	'',	0,	1),
(86,	'备份数据库',	68,	0,	'Database/index?type=export',	0,	'',	'数据备份',	0,	1),
(87,	'备份',	86,	0,	'Database/export',	0,	'备份数据库',	'',	0,	1),
(88,	'优化表',	86,	0,	'Database/optimize',	0,	'优化数据表',	'',	0,	1),
(89,	'修复表',	86,	0,	'Database/repair',	0,	'修复数据表',	'',	0,	1),
(90,	'还原数据库',	68,	0,	'Database/index?type=import',	0,	'',	'数据备份',	0,	1),
(91,	'恢复',	90,	0,	'Database/import',	0,	'数据库恢复',	'',	0,	1),
(92,	'删除',	90,	0,	'Database/del',	0,	'删除备份文件',	'',	0,	1),
(93,	'其他',	0,	5,	'other',	1,	'',	'',	0,	1),
(96,	'新增',	75,	0,	'Menu/add',	0,	'',	'系统设置',	0,	1),
(98,	'编辑',	75,	0,	'Menu/edit',	0,	'',	'',	0,	1),
(106,	'行为日志',	16,	0,	'Action/actionlog',	0,	'',	'行为管理',	0,	1),
(108,	'修改密码',	16,	0,	'User/updatePassword',	1,	'',	'',	0,	1),
(109,	'修改昵称',	16,	0,	'User/updateNickname',	1,	'',	'',	0,	1),
(110,	'查看行为日志',	106,	0,	'action/edit',	1,	'',	'',	0,	1),
(112,	'新增数据',	58,	0,	'think/add',	1,	'',	'',	0,	1),
(113,	'编辑数据',	58,	0,	'think/edit',	1,	'',	'',	0,	1),
(114,	'导入',	75,	0,	'Menu/import',	0,	'',	'',	0,	1),
(115,	'生成',	58,	0,	'Model/generate',	0,	'',	'',	0,	1),
(116,	'新增钩子',	57,	0,	'Addons/addHook',	0,	'',	'',	0,	1),
(117,	'编辑钩子',	57,	0,	'Addons/edithook',	0,	'',	'',	0,	1),
(118,	'文档排序',	3,	0,	'Article/sort',	1,	'',	'',	0,	1),
(119,	'排序',	70,	0,	'Config/sort',	1,	'',	'',	0,	1),
(120,	'排序',	75,	0,	'Menu/sort',	1,	'',	'',	0,	1),
(121,	'排序',	76,	0,	'Channel/sort',	1,	'',	'',	0,	1),
(122,	'数据列表',	58,	0,	'think/lists',	1,	'',	'',	0,	1),
(123,	'审核列表',	3,	0,	'Article/examine',	1,	'',	'',	0,	1);

DROP TABLE IF EXISTS `db_user`;
CREATE TABLE `db_user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `status` int(10) NOT NULL DEFAULT '0' COMMENT '-1：已删除，0：禁用，1：正常',
  `create_time` int(10) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `type` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0：普通用户，1：管理员',
  `mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号，唯一性标识',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT '密码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile` (`mobile`) COMMENT '手机号不能重复'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `db_user` (`id`, `name`, `status`, `create_time`, `update_time`, `type`, `mobile`, `password`) VALUES
(1,	'wangjialin',	1,	0,	0,	0,	'13521354092',	'14e1b600b1fd579f47433b88e8d85291');

-- 2017-11-03 01:18:25