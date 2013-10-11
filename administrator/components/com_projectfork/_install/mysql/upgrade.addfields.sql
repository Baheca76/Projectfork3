ALTER TABLE `#__pf_groups` ADD `permissions` TEXT NOT NULL AFTER `project`;
ALTER TABLE `#__pf_panels` ADD `caching` TINYINT( 1 ) NOT NULL AFTER `install_date`;
ALTER TABLE `#__pf_panels` ADD `cache_trigger` TEXT NOT NULL AFTER `caching`;
ALTER TABLE `#__pf_projects` ADD `category` VARCHAR( 124 ) NOT NULL AFTER `email`;