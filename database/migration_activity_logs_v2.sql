ALTER TABLE activity_logs 
ADD COLUMN record_owner_id INT DEFAULT NULL AFTER username,
ADD COLUMN record_owner_name VARCHAR(200) DEFAULT NULL AFTER record_owner_id;
