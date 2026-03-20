-- Migration to schema v2 (users + user-submitted news moderation)
-- Run this if you already imported the old install.sql

USE newsportal;

-- Users: add role=user, activation, verification
ALTER TABLE users
  MODIFY role ENUM('admin','user') NOT NULL DEFAULT 'user';

-- Run these only if columns don't exist yet:
ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;
ALTER TABLE users ADD COLUMN email_verified_at DATETIME NULL AFTER is_active;

-- News: add author + moderation fields
-- Run these only if columns don't exist yet:
ALTER TABLE news ADD COLUMN author_user_id INT UNSIGNED NULL AFTER country_id;
ALTER TABLE news ADD COLUMN status ENUM('pending','published','rejected') NOT NULL DEFAULT 'published' AFTER author_user_id;
ALTER TABLE news ADD COLUMN approved_by INT UNSIGNED NULL AFTER status;
ALTER TABLE news ADD COLUMN approved_at DATETIME NULL AFTER approved_by;

-- Add indexes (skip if they already exist)
CREATE INDEX idx_news_status_published ON news(status, published_at);
CREATE INDEX idx_news_author_created ON news(author_user_id, created_at);

-- Foreign keys (skip if already exist)
-- MySQL doesn't support IF NOT EXISTS for FK, so wrap in manual checks if needed.
-- If this fails, you can add them in phpMyAdmin GUI.

