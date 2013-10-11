DROP TABLE IF EXISTS `#__pf_mod_files`;
DROP TABLE IF EXISTS `#__pf_group_permissions`;

CREATE TABLE `#__pf_mod_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `filepath` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(6))
);