INSERT INTO `lst_error_status` (`id`, `status`, `ins_dt`, `ins_process_id`, `upd_dt`, `upd_process_id`, `del_flag`)
VALUES ('-1', '', now(), 'nevoral', now(), NULL, '1');

INSERT INTO `lst_error_tp` (`id`, `code`, `name`, `ins_dt`, `ins_process_id`, `upd_dt`, `upd_process_id`, `del_flag`)
VALUES ('-1', NULL, NULL, now(), 'nevoral', now(), NULL, '1');

INSERT INTO `project` (`id`, `name`, `data_source`, `ins_dt`, `ins_process_id`, `upd_dt`, `upd_process_id`, `del_flag`)
VALUES ('-1', '', '', now(), 'nevoral', now(), NULL, '1');


ALTER TABLE `error`
CHANGE `project_id` `project_id` int(11) NOT NULL DEFAULT '-1' AFTER `id`,
CHANGE `error_status_id` `error_status_id` int(11) NOT NULL DEFAULT '-1' AFTER `project_id`,
COMMENT='';

INSERT INTO `error` (`id`, `project_id`, `error_status_id`, `title`, `message`, `source_file`, `remote_file`, `error_dt`, `ins_dt`, `ins_process_id`, `upd_dt`, `upd_process_id`, `del_flag`)
VALUES ('-1', '-1', '-1', '', '', '', '', now(), now(), 'nevoral', now(), NULL, '1');


CREATE TABLE `error_reporting_service_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_id` int(11) NOT NULL DEFAULT '-1',
  `reporting_service_id` int(11) NOT NULL DEFAULT '-1',
  `reported_flag` tinyint(4) NOT NULL DEFAULT '0',
  `ins_dt` datetime NOT NULL,
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `error_id_reporting_service_id` (`error_id`,`reporting_service_id`),
  KEY `upd_dt` (`upd_dt`),
  KEY `reporting_service_id` (`reporting_service_id`),
  CONSTRAINT `error_reporting_service_rel_ibfk_1` FOREIGN KEY (`error_id`) REFERENCES `error` (`id`),
  CONSTRAINT `error_reporting_service_rel_ibfk_2` FOREIGN KEY (`reporting_service_id`) REFERENCES `lst_reporting_service` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `lst_reporting_service`;
CREATE TABLE `lst_reporting_service` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `upd_dt` (`upd_dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `lst_error_status`
ADD `code` varchar(255) NULL AFTER `id`,
CHANGE `status` `status` varchar(255) COLLATE 'utf8_general_ci' NULL AFTER `code`,
COMMENT='';

UPDATE `lst_error_status` SET `code` = 'NEW' WHERE `id` = '1';
UPDATE `lst_error_status` SET `code` = 'ARCHIVED' WHERE `id` = '2';