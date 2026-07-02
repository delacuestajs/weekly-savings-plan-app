-- Migration: Rename groups to bags (groups is a reserved word in MySQL)

-- 1. Rename foreign key columns
ALTER TABLE users DROP FOREIGN KEY IF EXISTS fk_users_group;
ALTER TABLE users CHANGE COLUMN group_id bag_id INT DEFAULT NULL;
ALTER TABLE users ADD CONSTRAINT fk_users_bag FOREIGN KEY (bag_id) REFERENCES `groups`(id) ON DELETE SET NULL;

ALTER TABLE savings DROP FOREIGN KEY IF EXISTS fk_savings_group;
ALTER TABLE savings CHANGE COLUMN group_id bag_id INT DEFAULT NULL;
ALTER TABLE savings ADD CONSTRAINT fk_savings_bag FOREIGN KEY (bag_id) REFERENCES `groups`(id) ON DELETE SET NULL;

ALTER TABLE activities DROP FOREIGN KEY IF EXISTS fk_activities_group;
ALTER TABLE activities CHANGE COLUMN group_id bag_id INT DEFAULT NULL;
ALTER TABLE activities ADD CONSTRAINT fk_activities_bag FOREIGN KEY (bag_id) REFERENCES `groups`(id) ON DELETE SET NULL;

ALTER TABLE expenses DROP FOREIGN KEY IF EXISTS fk_expenses_group;
ALTER TABLE expenses CHANGE COLUMN group_id bag_id INT DEFAULT NULL;
ALTER TABLE expenses ADD CONSTRAINT fk_expenses_bag FOREIGN KEY (bag_id) REFERENCES `groups`(id) ON DELETE SET NULL;

ALTER TABLE activity_logs DROP FOREIGN KEY IF EXISTS fk_logs_group;
ALTER TABLE activity_logs CHANGE COLUMN group_id bag_id INT DEFAULT NULL;
ALTER TABLE activity_logs ADD CONSTRAINT fk_logs_bag FOREIGN KEY (bag_id) REFERENCES `groups`(id) ON DELETE SET NULL;

-- 2. Rename the table itself
RENAME TABLE `groups` TO bags;
