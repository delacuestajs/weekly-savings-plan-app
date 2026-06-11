USE savings_db;

ALTER TABLE savings ADD COLUMN attachment VARCHAR(500) DEFAULT NULL AFTER description;
