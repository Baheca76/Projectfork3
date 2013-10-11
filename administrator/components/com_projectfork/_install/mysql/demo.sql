INSERT INTO `#__pf_projects` VALUES
(NULL, 'Web Design Project', '<p>Working on a cool new design! </p>', {uid}, '', '', '', '', '', 0, 0, 0, 1, {now}, 0);

INSERT INTO `#__pf_project_members` VALUES
(NULL, 1, 62, 1);

INSERT INTO `#__pf_tasks` VALUES
(NULL, 'Setup meeting', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 1, 1),
(NULL, 'Initial Statement of Work', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 1, 2),
(NULL, 'Finalize Statement of Work', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 1, 3),
(NULL, 'Invite team to project manager', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 1, 4),
(NULL, 'Define target audience', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 2, 1),
(NULL, 'Research competing sites and similar verticals', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 2, 2),
(NULL, 'Deliver preliminary design(s)', '', {uid}, 1, {now}, {now}, {now}, 1249686000, 0, 0, 2, 3),
(NULL, 'Revise design', '', {uid}, 1, 1248106627, 0, {now}, {now}, {now}, 0, 2, 4),
(NULL, 'Deliver final homepage design', '', {uid}, 1, {now}, {now}, {now}, 1250118000, 0, 0, 2, 5),
(NULL, 'Slice up html/css page', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 3, 1),
(NULL, 'Convert html page to template', '', {uid}, 1, {now}, {now}, {now}, 0, 0, 0, 3, 2),
(NULL, 'Add core Joomla! CSS', '', {uid}, 1, {now}, {now}, {now}, {now}, 0, 0, 3, 3),
(NULL, 'Add 3rd party extensions CSS', '', {uid}, 1, {now}, 0, 1248106827, {now}, 0, 0, 3, 4),
(NULL, 'Create conditionals for alternate homepage layout', '', 62, 1, {now}, {now}, 1248106848, 0, 0, 0, 3, 5),
(NULL, 'Deliver custom Joomla! template', '', {uid}, 1, {now}, {now}, {now}, 1251500400, 0, 0, 3, 6);

INSERT INTO `#__pf_task_attachments` VALUES
(NULL, 16, 1, 'folder');

INSERT INTO `#__pf_events` VALUES
(NULL, 'Kickoff Meeting', '', {uid}, 1, {now}, {now}, {future});

INSERT INTO `#__pf_folders` VALUES
(NULL, 'Design Files', 'Source Artwork', {uid}, 1, {now}, {now}),
(NULL, 'Development Files', 'Extensions, etc.', {uid}, 1, {now}, {now});

INSERT INTO `#__pf_folder_tree` VALUES
(NULL, 1, 0),
(NULL, 2, 0);

INSERT INTO `#__pf_milestones` VALUES
(NULL, 'Project Kickoff', 'Get everything started', 1, 0, {uid}, {now}, 0, 1),
(NULL, 'Website Design', 'Design Phase of project', 1, 0, {uid}, {now}, 0, 2),
(NULL, 'Create Joomla! Template', 'Construct template based off design', 1, 0, {uid}, {now}, 0, 3);

INSERT INTO `#__pf_time_tracking` VALUES
(NULL, 3, 1, {uid}, 'Core styles included', {now}, 60),
(NULL, 2, 1, {uid}, 'Rough pass', {now}, 120),
(NULL, 4, 1, {uid}, 'Projectfork made it easy!', {now}, 15),
(NULL, 5, 1, {uid}, 'Researching tons of sites', {now}, 140);

INSERT INTO `#__pf_topics` VALUES
(NULL, 'Functional Specs', '<p>Our understanding is that this Joomla! template must work with:</p><ul><li>Core Joomla! featureset</li><li>JCal Pro</li><li>Kunena</li><li>Community Builder</li><li>Phoca Gallery </li></ul> <p>If there are any others needed, please submit a change request. </p>', {uid}, 1, {now}, {now});