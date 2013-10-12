-- Adminer 3.7.0 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+07:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `aa`;
CREATE TABLE `aa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `upd_dt` (`upd_dt`),
  CONSTRAINT `aa_ibfk_1` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `aa_ibfk_2` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `changelog`;
CREATE TABLE `changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `query` mediumtext COLLATE utf8_czech_ci NOT NULL,
  `error` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `executed` tinyint(1) DEFAULT '0',
  `ins_timestamp` int(11) DEFAULT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) CHARACTER SET latin1 NOT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `upd_dt` (`upd_dt`),
  CONSTRAINT `changelog_ibfk_1` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `changelog_ibfk_2` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `cron`;
CREATE TABLE `cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `important` tinyint(1) NOT NULL DEFAULT '0',
  `task` varchar(255) NOT NULL,
  `alias` varchar(50) NOT NULL,
  `time` varchar(40) DEFAULT NULL,
  `expected_values_sql` text,
  `return_values_sql` text,
  `running_flag` tinyint(4) DEFAULT '0',
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `upd_dt` (`upd_dt`),
  CONSTRAINT `cron_ibfk_1` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `cron_ibfk_2` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text NOT NULL,
  `type` varchar(64) NOT NULL,
  `param1` text,
  `param2` text,
  `param3` text,
  `elapsed` float DEFAULT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `log_ibfk_2` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `log_cron`;
CREATE TABLE `log_cron` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cron_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `finish_time` datetime DEFAULT NULL,
  `return_value` text,
  `output` text,
  `successful_flag` tinyint(1) NOT NULL DEFAULT '0',
  `skipped_flag` tinyint(1) NOT NULL DEFAULT '0',
  `reported_flag` tinyint(1) NOT NULL DEFAULT '0',
  `manual_flag` tinyint(1) NOT NULL DEFAULT '0',
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cron_id` (`cron_id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  CONSTRAINT `log_cron_ibfk_1` FOREIGN KEY (`cron_id`) REFERENCES `cron` (`id`),
  CONSTRAINT `log_cron_ibfk_2` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `log_cron_ibfk_3` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `log_error`;
CREATE TABLE `log_error` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `assigned_user_id` int(11) NOT NULL DEFAULT '-1',
  `error_tp_id` int(11) NOT NULL,
  `log_visit_id` int(11) NOT NULL DEFAULT '-1',
  `message` text NOT NULL,
  `file_content` mediumtext,
  `url` text NOT NULL,
  `post_query` text,
  `reported_flag` tinyint(1) NOT NULL DEFAULT '0',
  `occured_cnt` int(11) unsigned NOT NULL DEFAULT '1',
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `error_tp_id` (`error_tp_id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `assigned_user_id` (`assigned_user_id`),
  CONSTRAINT `log_error_ibfk_1` FOREIGN KEY (`error_tp_id`) REFERENCES `lst_error_tp` (`id`),
  CONSTRAINT `log_error_ibfk_2` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `log_error_ibfk_3` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `log_error_ibfk_4` FOREIGN KEY (`assigned_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `log_error_log_cron_rel`;
CREATE TABLE `log_error_log_cron_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_error_id` int(11) NOT NULL,
  `log_cron_id` int(11) NOT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `log_error_id` (`log_error_id`),
  KEY `log_cron_id` (`log_cron_id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  CONSTRAINT `log_error_log_cron_rel_ibfk_1` FOREIGN KEY (`log_cron_id`) REFERENCES `log_cron` (`id`),
  CONSTRAINT `log_error_log_cron_rel_ibfk_2` FOREIGN KEY (`log_error_id`) REFERENCES `log_error` (`id`),
  CONSTRAINT `log_error_log_cron_rel_ibfk_3` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `log_error_log_cron_rel_ibfk_4` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `log_task_queue`;
CREATE TABLE `log_task_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_name` varchar(50) NOT NULL,
  `param` text,
  `start_time` datetime DEFAULT NULL,
  `finish_time` datetime DEFAULT NULL,
  `cmd` text,
  `stdout` text,
  `stderr` text,
  `successful_flag` tinyint(1) NOT NULL DEFAULT '0',
  `manual_flag` tinyint(1) NOT NULL DEFAULT '0',
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `task_name` (`task_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `log_visit`;
CREATE TABLE `log_visit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL DEFAULT '-1',
  `ajax_flag` tinyint(4) NOT NULL DEFAULT '-1',
  `api_flag` tinyint(4) NOT NULL DEFAULT '-1',
  `important_flag` tinyint(4) NOT NULL DEFAULT '-1',
  `url` varchar(255) DEFAULT NULL,
  `http_method` varchar(10) DEFAULT NULL,
  `http_get` mediumtext,
  `http_post` longtext,
  `remote_ip` varchar(50) DEFAULT NULL,
  `server_ip` varchar(20) DEFAULT NULL,
  `user_agent` varchar(50) DEFAULT NULL,
  `referral` varchar(50) DEFAULT NULL,
  `error_msg` mediumtext,
  `api_response` mediumtext,
  `elapsed` float DEFAULT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(50) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(50) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `log_visit_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lst_error_tp`;
CREATE TABLE `lst_error_tp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `AK_LST_ERROR_TP` (`code`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `upd_dt` (`upd_dt`),
  CONSTRAINT `lst_error_tp_ibfk_1` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `lst_error_tp_ibfk_2` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lst_lang`;
CREATE TABLE `lst_lang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `iso_6391_code` varchar(2) DEFAULT NULL,
  `iso_6392_code` varchar(3) DEFAULT NULL,
  `auto_translate_flag` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'Auto Translate with Gengo or Google Translate',
  `active_flag` tinyint(4) NOT NULL DEFAULT '0',
  `flag_url` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT '0',
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `AK_LST_LANG` (`code`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `upd_dt` (`upd_dt`),
  CONSTRAINT `lst_lang_ibfk_1` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `lst_lang_ibfk_2` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) NOT NULL DEFAULT '-1',
  `full_name` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `fb_uid` bigint(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_user_id` int(11) NOT NULL DEFAULT '-1',
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_user_id` int(11) NOT NULL DEFAULT '-1',
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `AK_USER` (`email`,`fb_uid`),
  KEY `lang_id` (`lang_id`),
  KEY `ins_user_id` (`ins_user_id`),
  KEY `upd_user_id` (`upd_user_id`),
  KEY `upd_dt` (`upd_dt`),
  CONSTRAINT `user_ibfk_4` FOREIGN KEY (`lang_id`) REFERENCES `lst_lang` (`id`),
  CONSTRAINT `user_ibfk_8` FOREIGN KEY (`ins_user_id`) REFERENCES `user` (`id`),
  CONSTRAINT `user_ibfk_9` FOREIGN KEY (`upd_user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2013-10-12 14:14:05