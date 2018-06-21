# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.6.22-log)
# Database: code_release
# Generation Time: 2018-06-21 08:08:39 +0000
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name_uniq` (`name`),
  UNIQUE KEY `project_repository_uniq` (`repository`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目表';

LOCK TABLES `project` WRITE;
/*!40000 ALTER TABLE `project` DISABLE KEYS */;

INSERT INTO `project` (`id`, `name`, `repository`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'mc3','git@192.168.175.129:MC3/mc3.git',1,'2018-06-13 09:15:36','2018-06-13 09:15:36'),
	(2,'mid_src','git@192.168.175.129:POS/MID_SRC.git',1,'2018-06-13 09:18:19','2018-06-13 09:28:27'),
	(3,'mpos_online_src','git@192.168.175.129:POS/MPOS_ONLNE/MPOS_ONLINE_SRC.git',1,'2018-06-13 09:22:00','2018-06-13 09:28:20');

/*!40000 ALTER TABLE `project` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table project_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_group`;

CREATE TABLE `project_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=有效 0=无效',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目组';

LOCK TABLES `project_group` WRITE;
/*!40000 ALTER TABLE `project_group` DISABLE KEYS */;

INSERT INTO `project_group` (`id`, `name`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'mc3',1,'2018-06-18 17:11:33','2018-06-18 17:11:33'),
	(2,'test',1,'2018-06-18 17:11:56','2018-06-18 17:11:56');

/*!40000 ALTER TABLE `project_group` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table project_group_combination
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_group_combination`;

CREATE TABLE `project_group_combination` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `project_id` int(11) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1=有效 0=无效',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目和项目组组合';

LOCK TABLES `project_group_combination` WRITE;
/*!40000 ALTER TABLE `project_group_combination` DISABLE KEYS */;

INSERT INTO `project_group_combination` (`id`, `group_id`, `project_id`, `status`, `created_at`, `updated_at`)
VALUES
	(1,1,1,1,'2018-06-19 08:58:37','2018-06-19 08:58:37'),
	(2,1,2,1,'2018-06-19 08:58:48','2018-06-19 08:58:48'),
	(3,1,3,1,'2018-06-19 08:58:54','2018-06-19 08:58:54'),
	(4,2,2,1,'2018-06-19 08:59:01','2018-06-19 08:59:01');

/*!40000 ALTER TABLE `project_group_combination` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table project_static_file
# ------------------------------------------------------------

DROP TABLE IF EXISTS `project_static_file`;

CREATE TABLE `project_static_file` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project_id` int(11) unsigned NOT NULL COMMENT '项目ID',
  `file_path` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1=有效 0=无效',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_static_files_uniq` (`project_id`,`file_path`),
  CONSTRAINT `project_static_files_project_fk` FOREIGN KEY (`project_id`) REFERENCES `project` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目的静态文件(用于从旧版本项目中复制)';

LOCK TABLES `project_static_file` WRITE;
/*!40000 ALTER TABLE `project_static_file` DISABLE KEYS */;

INSERT INTO `project_static_file` (`id`, `project_id`, `file_path`, `status`, `created_at`, `updated_at`)
VALUES
	(1,1,'config/database.php',1,'2018-06-13 09:30:34','2018-06-13 09:30:53'),
	(2,2,'saas.php',1,'2018-06-13 09:31:07','2018-06-13 09:31:07'),
	(3,2,'saas_config.php',1,'2018-06-13 09:31:15','2018-06-13 09:31:15'),
	(4,2,'config.php',1,'2018-06-13 09:31:22','2018-06-13 09:31:22'),
	(5,3,'script/saas.js',1,'2018-06-13 09:31:31','2018-06-13 09:31:31');

/*!40000 ALTER TABLE `project_static_file` ENABLE KEYS */;
UNLOCK TABLES;


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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `host` (`host`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务器列表';

LOCK TABLES `server` WRITE;
/*!40000 ALTER TABLE `server` DISABLE KEYS */;

INSERT INTO `server` (`id`, `name`, `host`, `user`, `password`, `port`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'够范185','120.27.133.185','root','',0,1,'2018-06-13 08:52:42','2018-06-21 09:50:06'),
	(2,'共享214','120.27.144.214','root','',0,1,'2018-06-13 08:54:35','2018-06-13 08:54:35'),
	(3,'千色店91','114.55.113.91','root','',0,1,'2018-06-13 08:54:35','2018-06-13 08:54:35');

/*!40000 ALTER TABLE `server` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table server_group
# ------------------------------------------------------------

DROP TABLE IF EXISTS `server_group`;

CREATE TABLE `server_group` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '组名称',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态 1=有效 0=无效',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务器组';

LOCK TABLES `server_group` WRITE;
/*!40000 ALTER TABLE `server_group` DISABLE KEYS */;

INSERT INTO `server_group` (`id`, `name`, `status`, `created_at`, `updated_at`)
VALUES
	(1,'mc3',1,'2018-06-18 17:12:10','2018-06-18 17:12:24'),
	(2,'test',1,'2018-06-18 17:12:21','2018-06-18 17:12:21');

/*!40000 ALTER TABLE `server_group` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table server_group_combination
# ------------------------------------------------------------

DROP TABLE IF EXISTS `server_group_combination`;

CREATE TABLE `server_group_combination` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL,
  `server_id` int(11) unsigned NOT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '状态1=有效 0=无效',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='服务器和服务器组组合';

LOCK TABLES `server_group_combination` WRITE;
/*!40000 ALTER TABLE `server_group_combination` DISABLE KEYS */;

INSERT INTO `server_group_combination` (`id`, `group_id`, `server_id`, `status`, `created_at`, `updated_at`)
VALUES
	(1,1,1,1,'2018-06-18 17:43:26','2018-06-18 17:43:26'),
	(2,1,2,1,'2018-06-18 17:43:40','2018-06-18 17:43:40'),
	(3,1,3,1,'2018-06-19 10:38:33','2018-06-19 10:38:33'),
	(4,2,1,1,'2018-06-19 10:38:38','2018-06-19 10:38:38');

/*!40000 ALTER TABLE `server_group_combination` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table task
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task`;

CREATE TABLE `task` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_group_id` int(11) unsigned NOT NULL,
  `task_server_id` int(11) unsigned NOT NULL,
  `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态:-10=任务报错 0=取消 10=任务创建 20=已上传至服务器 30=已解压并部署 40=完成（已保留版本）',
  `prev_status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '前一状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `params` text COLLATE utf8mb4_unicode_ci COMMENT '任务参数json之后的字符串',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='任务组（负责本地项目文件打包，上传到服务器）';



# Dump of table task_log
# ------------------------------------------------------------

DROP TABLE IF EXISTS `task_log`;

CREATE TABLE `task_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `task_group_id` int(11) unsigned NOT NULL,
  `task_id` int(11) unsigned DEFAULT NULL,
  `type` tinyint(3) unsigned NOT NULL DEFAULT '1' COMMENT '任务类型 1=组任务 2=子任务',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
