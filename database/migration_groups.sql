-- Migration: Groups & Superadmin Role
-- Creates groups table, adds group_id to all tables, promotes admin to superadmin

-- 1. Create groups table
CREATE TABLE IF NOT EXISTS `groups` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    status TINYINT DEFAULT 1,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Insert default group
INSERT INTO `groups` (name, description) VALUES ('Default', 'Default group for existing users and records');

-- 3. Add group_id to users table
ALTER TABLE users ADD COLUMN group_id INT DEFAULT NULL AFTER role;
ALTER TABLE users ADD CONSTRAINT fk_users_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL;

-- 4. Add group_id to savings table
ALTER TABLE savings ADD COLUMN group_id INT DEFAULT NULL AFTER user_id;
ALTER TABLE savings ADD CONSTRAINT fk_savings_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL;

-- 5. Add group_id to activities table
ALTER TABLE activities ADD COLUMN group_id INT DEFAULT NULL AFTER id;
ALTER TABLE activities ADD CONSTRAINT fk_activities_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL;

-- 6. Add group_id to expenses table (via activities, but store for direct filtering)
ALTER TABLE expenses ADD COLUMN group_id INT DEFAULT NULL AFTER activity_id;
ALTER TABLE expenses ADD CONSTRAINT fk_expenses_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL;

-- 7. Add group_id to activity_logs table
ALTER TABLE activity_logs ADD COLUMN group_id INT DEFAULT NULL AFTER user_id;
ALTER TABLE activity_logs ADD CONSTRAINT fk_logs_group FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL;

-- 8. Set all existing records to default group (id=1)
UPDATE users SET group_id = 1 WHERE group_id IS NULL;
UPDATE savings SET group_id = 1 WHERE group_id IS NULL;
UPDATE activities SET group_id = 1 WHERE group_id IS NULL;
UPDATE expenses SET group_id = 1 WHERE group_id IS NULL;
UPDATE activity_logs SET group_id = 1 WHERE group_id IS NULL;

-- 9. Promote admin user to superadmin (role=3)
-- The 'admin' user with role=2 becomes role=3 (superadmin)
UPDATE users SET role = 3 WHERE username = 'admin' AND role = 2;
