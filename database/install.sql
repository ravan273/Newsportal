-- News Portal (Nepal + World) database schema
-- Create DB then import this file in phpMyAdmin.

CREATE DATABASE IF NOT EXISTS newsportal
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE newsportal;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  email_verified_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_slug (slug),
  KEY idx_categories_active_sort (is_active, sort_order, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS countries (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(140) NOT NULL,
  iso2 CHAR(2) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_countries_slug (slug),
  UNIQUE KEY uq_countries_iso2 (iso2),
  KEY idx_countries_active_name (is_active, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS news (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(220) NOT NULL,
  slug VARCHAR(260) NOT NULL,
  summary TEXT NULL,
  content LONGTEXT NOT NULL,
  image_path VARCHAR(255) NULL,
  category_id INT UNSIGNED NULL,
  country_id INT UNSIGNED NULL,
  author_user_id INT UNSIGNED NULL,
  status ENUM('pending','published','rejected') NOT NULL DEFAULT 'published',
  approved_by INT UNSIGNED NULL,
  approved_at DATETIME NULL,
  source_url VARCHAR(500) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  published_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_news_slug (slug),
  KEY idx_news_published (published_at),
  KEY idx_news_status_published (status, published_at),
  KEY idx_news_country_published (country_id, published_at),
  KEY idx_news_category_published (category_id, published_at),
  KEY idx_news_author_created (author_user_id, created_at),
  FULLTEXT KEY ft_news_title_content (title, content),
  CONSTRAINT fk_news_category FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_news_country FOREIGN KEY (country_id) REFERENCES countries(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_news_author FOREIGN KEY (author_user_id) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT fk_news_approved_by FOREIGN KEY (approved_by) REFERENCES users(id)
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed minimal data (Nepal + a few categories)
INSERT IGNORE INTO countries (id, name, slug, iso2, is_active) VALUES
  (1, 'Nepal', 'nepal', 'NP', 1),
  (2, 'India', 'india', 'IN', 1),
  (3, 'United States', 'united-states', 'US', 1),
  (4, 'United Kingdom', 'united-kingdom', 'GB', 1),
  (5, 'China', 'china', 'CN', 1);

INSERT IGNORE INTO categories (id, name, slug, sort_order, is_active) VALUES
  (1, 'Politics', 'politics', 1, 1),
  (2, 'Business', 'business', 2, 1),
  (3, 'Sports', 'sports', 3, 1),
  (4, 'Technology', 'technology', 4, 1),
  (5, 'Entertainment', 'entertainment', 5, 1);

-- Default admin:
-- email: admin@newsportal.local
-- password: admin123
-- (Change after first login)
INSERT IGNORE INTO users (id, name, email, password_hash, role) VALUES
  (1, 'Admin', 'admin@newsportal.local', '$2y$10$5A7FCB3jKyex1KzN8U0sxOtq0yOOvbsA51JghB4rxmYGTYwtZthTq', 'admin');

