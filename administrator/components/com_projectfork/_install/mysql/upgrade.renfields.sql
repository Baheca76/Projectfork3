ALTER TABLE `#__pf_mods` CHANGE `published` `enabled` INT( 1 ) NOT NULL;
ALTER TABLE `#__pf_panels` CHANGE `published` `enabled` INT( 1 ) NOT NULL;
ALTER TABLE `#__pf_processes` CHANGE `published` `enabled` INT( 1 ) NOT NULL;
ALTER TABLE `#__pf_sections` CHANGE `published` `enabled` INT( 1 ) NOT NULL;
ALTER TABLE `#__pf_themes` CHANGE `published` `enabled` INT( 1 ) NOT NULL;
ALTER TABLE `#__pf_projects` CHANGE `archivated` `archived` INT( 1 ) NOT NULL;
ALTER TABLE `#__pf_user_profile` CHANGE `value` `content` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `#__pf_processes` CHANGE `position` `event` VARCHAR( 255 ) NOT NULL; 