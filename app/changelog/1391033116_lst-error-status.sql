SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `lst_error_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(100) NOT NULL,
  `ins_dt` datetime NOT NULL,
  `ins_process_id` varchar(255) DEFAULT NULL,
  `upd_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `upd_process_id` varchar(255) DEFAULT NULL,
  `del_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `upd_dt` (`upd_dt`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

INSERT INTO `lst_error_status` (`id`, `status`, `ins_dt`, `ins_process_id`, `upd_dt`, `upd_process_id`, `del_flag`) VALUES
(1, 'New', '0000-00-00 00:00:00', NULL, '2014-01-29 22:04:11', NULL, 0),
(2, 'Archived', '0000-00-00 00:00:00', NULL, '2014-01-29 22:04:11', NULL, 0);

ALTER TABLE `error` DROP `solved_flag`;
ALTER TABLE `error` ADD `error_status_id` INT NOT NULL AFTER `project_id`;
UPDATE `error` SET `error_status_id` = 1;
ALTER TABLE `error` ADD FOREIGN KEY ( `error_status_id` ) REFERENCES `lst_error_status` ( `id` );

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
