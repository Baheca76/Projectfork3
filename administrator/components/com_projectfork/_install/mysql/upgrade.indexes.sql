ALTER TABLE `#__pf_comments` DROP INDEX `scope`;
ALTER TABLE `#__pf_comments` ADD INDEX `idx_scopeid` ( `scope` ( 12 ) , `item_id` );
ALTER TABLE `#__pf_comments` ADD INDEX `idx_author` ( `author` );
ALTER TABLE `#__pf_comments` ADD INDEX `idx_scope` ( `scope` ( 12 ) );
ALTER TABLE `#__pf_comments` ADD INDEX `idx_itemid` ( `item_id` );
ANALYZE TABLE `#__pf_comments`;
OPTIMIZE TABLE `#__pf_comments`;

ALTER TABLE `#__pf_files` DROP INDEX `project`;
ALTER TABLE `#__pf_files` ADD INDEX `idx_project` ( `project` );
ALTER TABLE `#__pf_files` ADD INDEX `idx_author` ( `author` );
ALTER TABLE `#__pf_files` ADD INDEX `idx_dir` ( `dir` );
ALTER TABLE `#__pf_files` ADD INDEX `idx_projectdir` ( `project` , `dir` );
ANALYZE TABLE `#__pf_files`;
OPTIMIZE TABLE `#__pf_files`;

ALTER TABLE `#__pf_folders` DROP INDEX `project`;
ALTER TABLE `#__pf_folders` ADD INDEX `idx_project` ( `project` );
ALTER TABLE `#__pf_folders` ADD INDEX `idx_author` ( `author` );
ANALYZE TABLE `#__pf_folders`;
OPTIMIZE TABLE `#__pf_folders`;

ALTER TABLE `#__pf_folder_tree` DROP INDEX `parent_id`;
ALTER TABLE `#__pf_folder_tree` ADD INDEX `idx_parentid` ( `parent_id` );
ALTER TABLE `#__pf_folder_tree` ADD INDEX `idx_folderid` ( `folder_id` );
ANALYZE TABLE `#__pf_folder_tree`;
OPTIMIZE TABLE `#__pf_folder_tree`;

ALTER TABLE `#__pf_group_users` DROP INDEX `group_id`;
ALTER TABLE `#__pf_group_users` ADD INDEX `idx_groupid` ( `group_id` );
ALTER TABLE `#__pf_group_users` ADD INDEX `idx_userid` ( `user_id` );
ANALYZE TABLE `#__pf_group_users`;
OPTIMIZE TABLE `#__pf_group_users`;

ALTER TABLE `#__pf_notes` DROP INDEX `project`;
ALTER TABLE `#__pf_notes` ADD INDEX `idx_project` ( `project` );
ALTER TABLE `#__pf_notes` ADD INDEX `idx_author` ( `author` );
ALTER TABLE `#__pf_notes` ADD INDEX `idx_dir` ( `dir` );
ALTER TABLE `#__pf_notes` ADD INDEX `idx_projectdir` ( `project` , `dir` );
ANALYZE TABLE `#__pf_notes`;
OPTIMIZE TABLE `#__pf_notes`;

ALTER TABLE `#__pf_projects` DROP INDEX `archivated`;
ALTER TABLE `#__pf_projects` ADD INDEX `idx_approved` ( `approved` );
ALTER TABLE `#__pf_projects` ADD INDEX `idx_archived` ( `archived` );
ALTER TABLE `#__pf_projects` ADD INDEX `idx_author` ( `author` );
ALTER TABLE `#__pf_projects` ADD INDEX `idx_ispublic` ( `is_public` );
ALTER TABLE `#__pf_projects` ADD INDEX `idx_category` ( `category` ( 12 ) ) ;
ANALYZE TABLE `#__pf_notes`;
OPTIMIZE TABLE `#__pf_notes`;

ALTER TABLE `#__pf_project_members` DROP INDEX `project_id`;
ALTER TABLE `#__pf_project_members` ADD INDEX `idx_projectid` ( `project_id` );
ALTER TABLE `#__pf_project_members` ADD INDEX `idx_userapproved` ( `user_id` , `approved` ) ;
ANALYZE TABLE `#__pf_project_members`;
OPTIMIZE TABLE `#__pf_project_members`;

ALTER TABLE `#__pf_tasks` DROP INDEX `project`;
ALTER TABLE `#__pf_tasks` ADD INDEX `idx_project` ( `project` );
ALTER TABLE `#__pf_tasks` ADD INDEX `idx_author` ( `author` );
ALTER TABLE `#__pf_tasks` ADD INDEX `idx_progress` ( `progress` );
ALTER TABLE `#__pf_tasks` ADD INDEX `idx_priority` ( `priority` );
ALTER TABLE `#__pf_tasks` ADD INDEX `idx_milestone` ( `milestone` );
ANALYZE TABLE `#__pf_tasks`;
OPTIMIZE TABLE `#__pf_tasks`;

ALTER TABLE `#__pf_task_users` DROP INDEX `user_id`;
ALTER TABLE `#__pf_task_users` ADD INDEX `idx_userid` ( `user_id` );
ALTER TABLE `#__pf_task_users` ADD INDEX `idx_taskid` ( `task_id` );
ANALYZE TABLE `#__pf_task_users`;
OPTIMIZE TABLE `#__pf_task_users`;

ALTER TABLE `#__pf_user_profile` DROP INDEX `user_id`;
ALTER TABLE `#__pf_user_profile` ADD INDEX `idx_param` ( `user_id` , `parameter` );
ALTER TABLE `#__pf_user_profile` ADD INDEX `idx_userid` ( `user_id` );