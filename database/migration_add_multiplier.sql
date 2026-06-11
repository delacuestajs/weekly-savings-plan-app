-- Migration: Move multiplier from savings to users table
-- Run this if you have an existing database

-- Add multiplier to users table
ALTER TABLE users ADD COLUMN multiplier INT DEFAULT 1 AFTER comments;

-- Remove multiplier from savings table (if exists)
-- ALTER TABLE savings DROP COLUMN multiplier;
