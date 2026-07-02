-- Migration: Add long_name and picture to bags table

ALTER TABLE bags ADD COLUMN long_name VARCHAR(255) DEFAULT NULL AFTER name;
ALTER TABLE bags ADD COLUMN picture VARCHAR(500) DEFAULT NULL AFTER description;
