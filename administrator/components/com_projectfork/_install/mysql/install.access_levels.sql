/* ACCESS LEVELS*/
INSERT INTO `#__pf_access_levels` VALUES
(1, 'PFL_ACL_VISITOR', 0, 0, ''),
(2, 'PFL_ACL_REGISTERED', 1, 0, ''),
(3, 'PFL_ACL_AUTHOR', 2, 0, ''),
(4, 'PFL_ACL_EDITOR', 3, 0, ''),
(5, 'PFL_ACL_PUBLISHER', 4, 0, ''),
(6, 'PFL_ACL_MANAGER', 5, 0, 'system_administrator'),
(7, 'PFL_ACL_ADMINISTRATOR', 999, 0, 'system_administrator'),
(8, 'PFL_ACL_SADMINISTRATOR', 999, 0, 'system_administrator'),
(9, 'PFL_ACL_PA', 6, 0, 'project_administrator'),
(10, 'PFL_ACL_PM', 1, 0, '');