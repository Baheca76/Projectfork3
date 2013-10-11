DROP TABLE IF EXISTS `#__pf_access_flags`;
CREATE TABLE IF NOT EXISTS `#__pf_access_flags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(124) NOT NULL,
  `title` varchar(124) NOT NULL,
  `project` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_access_levels`;
CREATE TABLE IF NOT EXISTS `#__pf_access_levels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `score` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `flag` varchar(124) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_comments`;
CREATE TABLE IF NOT EXISTS `#__pf_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `scope` varchar(255) NOT NULL,
  `item_id` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_author` (`author`),
  KEY `idx_scopeid` (`scope`(12),`item_id`),
  KEY `idx_scope` (`scope`(12)),
  KEY `idx_itemid` (`item_id`)
);

DROP TABLE IF EXISTS `#__pf_events`;
CREATE TABLE IF NOT EXISTS `#__pf_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(56) NOT NULL,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `sdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_files`;
CREATE TABLE IF NOT EXISTS `#__pf_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `prefix` varchar(255) NOT NULL,
  `description` varchar(128) NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `dir` int(11) NOT NULL,
  `filesize` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project`),
  KEY `idx_author` (`author`),
  KEY `idx_dir` (`dir`),
  KEY `idx_projectdir` (`project`,`dir`)
);

DROP TABLE IF EXISTS `#__pf_folders`;
CREATE TABLE IF NOT EXISTS `#__pf_folders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(56) NOT NULL,
  `description` varchar(128) NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_folder_tree`;
CREATE TABLE IF NOT EXISTS `#__pf_folder_tree` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folder_id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_parentid` (`parent_id`),
  KEY `idx_folderid` (`folder_id`)
);

DROP TABLE IF EXISTS `#__pf_groups`;
CREATE TABLE IF NOT EXISTS `#__pf_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `description` varchar(255) NOT NULL,
  `project` int(11) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_group_users`;
CREATE TABLE IF NOT EXISTS `#__pf_group_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_groupid` (`group_id`),
  KEY `idx_userid` (`user_id`)
);

DROP TABLE IF EXISTS `#__pf_languages`;
CREATE TABLE IF NOT EXISTS `#__pf_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `published` int(1) NOT NULL,
  `is_default` int(1) NOT NULL,
  `author` varchar(56) NOT NULL,
  `email` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(24) NOT NULL,
  `license` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `create_date` varchar(56) NOT NULL,
  `install_date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`published`)
);

DROP TABLE IF EXISTS `#__pf_milestones`;
CREATE TABLE IF NOT EXISTS `#__pf_milestones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `content` varchar(255) NOT NULL,
  `project` int(11) NOT NULL,
  `priority` int(1) NOT NULL,
  `author` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_mods`;
CREATE TABLE IF NOT EXISTS `#__pf_mods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `enabled` int(1) NOT NULL,
  `author` varchar(56) NOT NULL,
  `email` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(24) NOT NULL,
  `license` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `create_date` varchar(56) NOT NULL,
  `install_date` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `published` (`enabled`)
);

DROP TABLE IF EXISTS `#__pf_mod_files`;
CREATE TABLE IF NOT EXISTS `#__pf_mod_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `filepath` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`(6))
);

DROP TABLE IF EXISTS `#__pf_notes`;
CREATE TABLE IF NOT EXISTS `#__pf_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(56) NOT NULL,
  `description` varchar(128) NOT NULL,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `dir` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project`),
  KEY `idx_author` (`author`),
  KEY `idx_dir` (`dir`),
  KEY `idx_projectdir` (`project`,`dir`)
);

DROP TABLE IF EXISTS `#__pf_panels`;
CREATE TABLE IF NOT EXISTS `#__pf_panels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `flag` varchar(124) NOT NULL,
  `enabled` int(1) NOT NULL,
  `position` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `author` varchar(56) NOT NULL,
  `email` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(24) NOT NULL,
  `license` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `create_date` varchar(56) NOT NULL,
  `install_date` int(11) NOT NULL,
  `caching` tinyint(1) NOT NULL,
  `cache_trigger` text NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `#__pf_processes`;
CREATE TABLE IF NOT EXISTS `#__pf_processes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `flag` varchar(124) NOT NULL,
  `enabled` int(1) NOT NULL,
  `event` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  `author` varchar(56) NOT NULL,
  `email` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(24) NOT NULL,
  `license` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `create_date` varchar(56) NOT NULL,
  `install_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `#__pf_projects`;
CREATE TABLE IF NOT EXISTS `#__pf_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `color` varchar(12) NOT NULL,
  `logo` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `email` varchar(124) NOT NULL,
  `category` varchar(124) NOT NULL,
  `is_public` int(1) NOT NULL,
  `allow_register` int(1) NOT NULL,
  `archived` int(1) NOT NULL,
  `approved` int(1) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_approved` (`approved`),
  KEY `idx_archived` (`archived`),
  KEY `idx_author` (`author`),
  KEY `idx_ispublic` (`is_public`),
  KEY `idx_category` (`category`(12))
);

DROP TABLE IF EXISTS `#__pf_project_invitations`;
CREATE TABLE IF NOT EXISTS `#__pf_project_invitations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `inv_id` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
);

DROP TABLE IF EXISTS `#__pf_project_members`;
CREATE TABLE IF NOT EXISTS `#__pf_project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `approved` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_projectid` (`project_id`),
  KEY `idx_userapproved` (`user_id`,`approved`)
);

DROP TABLE IF EXISTS `#__pf_sections`;
CREATE TABLE IF NOT EXISTS `#__pf_sections` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `enabled` int(1) NOT NULL,
  `score` int(11) NOT NULL,
  `flag` varchar(124) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `is_default` int(1) NOT NULL,
  `ordering` int(11) NOT NULL,
  `author` varchar(56) NOT NULL,
  `email` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(24) NOT NULL,
  `license` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `create_date` varchar(56) NOT NULL,
  `install_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `#__pf_section_tasks`;
CREATE TABLE IF NOT EXISTS `#__pf_section_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `section` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `score` int(11) NOT NULL,
  `flag` varchar(255) NOT NULL,
  `tags` varchar(255) NOT NULL,
  `parent` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `#__pf_settings`;
CREATE TABLE IF NOT EXISTS `#__pf_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parameter` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `scope` varchar(124) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_scope` (`scope`(12)),
  KEY `idx_paramscope` (`parameter`(12),`scope`(12)),
  KEY `idx_parameter` (`parameter`(12))
);

DROP TABLE IF EXISTS `#__pf_tasks`;
CREATE TABLE IF NOT EXISTS `#__pf_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `mdate` int(11) NOT NULL,
  `sdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  `progress` int(11) NOT NULL,
  `priority` int(11) NOT NULL,
  `milestone` int(11) NOT NULL,
  `ordering` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_project` (`project`),
  KEY `idx_author` (`author`),
  KEY `idx_progress` (`progress`),
  KEY `idx_priority` (`priority`),
  KEY `idx_milestone` (`milestone`)
);

DROP TABLE IF EXISTS `#__pf_task_attachments`;
CREATE TABLE IF NOT EXISTS `#__pf_task_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `attach_id` int(11) NOT NULL,
  `attach_type` varchar(24) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task_id` (`task_id`)
);

DROP TABLE IF EXISTS `#__pf_task_users`;
CREATE TABLE IF NOT EXISTS `#__pf_task_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_userid` (`user_id`),
  KEY `idx_taskid` (`task_id`)
);

DROP TABLE IF EXISTS `#__pf_themes`;
CREATE TABLE IF NOT EXISTS `#__pf_themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `enabled` int(1) NOT NULL,
  `is_default` int(1) NOT NULL,
  `author` varchar(56) NOT NULL,
  `email` varchar(124) NOT NULL,
  `website` varchar(255) NOT NULL,
  `version` varchar(24) NOT NULL,
  `license` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `create_date` varchar(56) NOT NULL,
  `install_date` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `#__pf_time_tracking`;
CREATE TABLE IF NOT EXISTS `#__pf_time_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` varchar(255) NOT NULL,
  `cdate` int(11) NOT NULL,
  `timelog` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `task` (`task_id`,`user_id`)
);

DROP TABLE IF EXISTS `#__pf_topics`;
CREATE TABLE IF NOT EXISTS `#__pf_topics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`)
);

DROP TABLE IF EXISTS `#__pf_topic_replies`;
CREATE TABLE IF NOT EXISTS `#__pf_topic_replies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(124) NOT NULL,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `project` int(11) NOT NULL,
  `topic` int(11) NOT NULL,
  `cdate` int(11) NOT NULL,
  `edate` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project` (`project`,`topic`)
);

DROP TABLE IF EXISTS `#__pf_topic_subscriptions`;
CREATE TABLE IF NOT EXISTS `#__pf_topic_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `topic_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`)
);

DROP TABLE IF EXISTS `#__pf_user_access_level`;
CREATE TABLE IF NOT EXISTS `#__pf_user_access_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `accesslvl` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
);

DROP TABLE IF EXISTS `#__pf_user_profile`;
CREATE TABLE IF NOT EXISTS `#__pf_user_profile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `parameter` varchar(64) NOT NULL,
  `content` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_param` (`user_id`,`parameter`(10)),
  KEY `idx_userid` (`user_id`)
);