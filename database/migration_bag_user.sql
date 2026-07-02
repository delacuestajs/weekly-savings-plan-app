-- Migration: Create bag_user pivot table for superadmin many-to-many relationship

CREATE TABLE IF NOT EXISTS bag_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bag_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bag_id) REFERENCES bags(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_bag_user (bag_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add current superadmin users to their current bag
INSERT INTO bag_user (bag_id, user_id)
SELECT bag_id, id FROM users WHERE role = 3 AND bag_id IS NOT NULL;
