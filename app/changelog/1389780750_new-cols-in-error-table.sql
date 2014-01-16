ALTER TABLE `error` ADD `source_file` VARCHAR( 255 ) NOT NULL AFTER `name` ;
ALTER TABLE `error` CHANGE `source` `remote_file` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `error` CHANGE `name` `title` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `error` ADD `message` VARCHAR( 255 ) NOT NULL AFTER `title` ;
ALTER TABLE `error` CHANGE `message` `message` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;