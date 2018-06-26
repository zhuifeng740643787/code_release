# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.22-log)
# Database: code_release
# Generation Time: 2018-06-26 06:11:21 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table project
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project`;

CREATE TABLE `project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '项目名称',
  `repository` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'git仓库地址',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=有效 2=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name_uniq` (`name`),
  UNIQUE KEY `project_repository_uniq` (`repository`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目表';



# Dump of table project_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_group`;

CREATE TABLE `project_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=有效 0=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目组';



# Dump of table project_group_combination
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_group_combination`;

CREATE TABLE `project_group_combination` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `project_id` int(11) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1=有效 0=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_group_combination_uniq` (`group_id`,`project_id`),
  KEY `project_group_combination_project_fk` (`project_id`),
  CONSTRAINT `project_group_combination_group_fk` FOREIGN KEY (`group_id`) REFERENCES `project_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `project_group_combination_project_fk` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目和项目组组合';



# Dump of table project_static_file
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_static_file`;

CREATE TABLE `project_static_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) unsigned NOT NULL COMMENT '项目ID',
  `file_path` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1=有效 0=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_static_files_uniq` (`project_id`,`file_path`),
  CONSTRAINT `project_static_files_project_fk` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目的静态文件(用于从旧版本项目中复制)';



# Dump of table server
# ------------------------------------------------------------

DROP TABLE IF EXISTS `server`;

CREATE TABLE `server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '服务器名称',
  `host` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'host',
  `user` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录用户名',
  `password` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `port` int(11) unsigned NOT NULL DEFAULT '22' COMMENT '端口号',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=有效 2=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务器列表';



# Dump of table server_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `server_group`;

CREATE TABLE `server_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=有效 0=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务器组';



# Dump of table server_group_combination
# ------------------------------------------------------------

DROP TABLE IF EXISTS `server_group_combination`;

CREATE TABLE `server_group_combination` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `server_id` int(11) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1=有效 0=无效',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `server_group_combination_uniq` (`group_id`,`server_id`),
  KEY `server_group_combination_server_fk` (`server_id`),
  CONSTRAINT `server_group_combination_group_fk` FOREIGN KEY (`group_id`) REFERENCES `server_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `server_group_combination_server_fk` FOREIGN KEY (`server_id`) REFERENCES `server` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务器和服务器组组合';



# Dump of table task
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task`;

CREATE TABLE `task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_group_id` int(11) unsigned NOT NULL,
  `task_server_id` int(11) unsigned NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态:-10=任务报错 0=取消 10=任务创建 20=已上传至服务器 30=已解压并部署 40=完成（已保留版本）',
  `prev_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '前一状态',
  `status_info` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态说明，如报错说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_uniq` (`task_group_id`,`task_server_id`),
  KEY `task_task_server_fk` (`task_server_id`),
  CONSTRAINT `task_task_group_fk` FOREIGN KEY (`task_group_id`) REFERENCES `task_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `task_task_server_fk` FOREIGN KEY (`task_server_id`) REFERENCES `task_server` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='子任务表（负责向服务器上传代码并部署）';



# Dump of table task_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task_group`;

CREATE TABLE `task_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version_num` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '任务版本',
  `status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '状态 -10=任务报错 0=已取消 10=任务创建 20=开始任务 30=代码复制完成 40=打包完成  50=子任务进行中 60=完成',
  `prev_status` tinyint(3) NOT NULL DEFAULT '10' COMMENT '前一状态',
  `status_info` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态说明，如报错说明',
  `release_code_path` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '代码发布目录',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '备注',
  `params` text COLLATE utf8mb4_unicode_ci COMMENT '任务参数json之后的字符串',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_group_uniq` (`version_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务组（负责本地项目文件打包，上传到服务器）';



# Dump of table task_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task_log`;

CREATE TABLE `task_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_group_id` int(11) unsigned NOT NULL,
  `task_id` int(11) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '任务类型 1=组任务 2=子任务',
  `status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '组任务状态 -10=任务报错 0=已取消 10=任务创建 20=开始任务 30=代码复制完成 40=打包完成  50=子任务进行中 60=完成;  子任务状态:-10=任务报错 0=取消 10=任务创建 20=已上传至服务器 30=已解压并部署 40=完成（已保留版本）',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_log_group_fk` (`task_group_id`),
  KEY `task_log_task_fk` (`task_id`),
  CONSTRAINT `task_log_group_fk` FOREIGN KEY (`task_group_id`) REFERENCES `task_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `task_log_task_fk` FOREIGN KEY (`task_id`) REFERENCES `task` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务日志';



# Dump of table task_project
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task_project`;

CREATE TABLE `task_project` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL COMMENT '组ID',
  `project_id` int(11) unsigned NOT NULL COMMENT '项目ID',
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '项目名称',
  `repository` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'git仓库地址',
  `release_type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '发布类型 1=branch 2=tag',
  `release_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '发布类型名称 分支名/标签名',
  `task_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '项目状态 -10=报错 0=创建完成 10=代码复制 20=切换分支/标签 30=文件替换 40=写入release日志文件',
  `prev_status` tinyint(3) NOT NULL DEFAULT '0' COMMENT '前一状态',
  `status_info` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '状态说明，如报错说明',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_project_uniq` (`group_id`,`project_id`),
  KEY `task_project_project_index` (`project_id`),
  CONSTRAINT `task_project_group_index` FOREIGN KEY (`group_id`) REFERENCES `task_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `task_project_project_index` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务项目';



# Dump of table task_project_replace_file
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task_project_replace_file`;

CREATE TABLE `task_project_replace_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_project_id` int(11) unsigned NOT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '文件类型 1=静态文件 2=上传文件',
  `local_file` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '本地文件',
  `replace_file` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '要替换的文件',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `task_project_replace_file_project_fk` (`task_project_id`),
  CONSTRAINT `task_project_replace_file_project_fk` FOREIGN KEY (`task_project_id`) REFERENCES `task_project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务项目替换文件';



# Dump of table task_server
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task_server`;

CREATE TABLE `task_server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `server_id` int(11) unsigned NOT NULL,
  `name` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '服务器名称',
  `host` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'host',
  `user` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录用户名',
  `password` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `port` int(11) unsigned NOT NULL DEFAULT '22' COMMENT '端口号',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_server_uniq` (`group_id`,`server_id`),
  KEY `task_server_server_index` (`server_id`),
  CONSTRAINT `task_server_group_index` FOREIGN KEY (`group_id`) REFERENCES `task_group` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `task_server_server_index` FOREIGN KEY (`server_id`) REFERENCES `server` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务-服务器';




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
