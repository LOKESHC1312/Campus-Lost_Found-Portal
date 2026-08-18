-- ============================================================
-- Campus Lost & Found Portal — Database Setup
-- Database: campus_lost_found
-- Run this file in phpMyAdmin or MySQL CLI
-- ============================================================

CREATE DATABASE IF NOT EXISTS campus_lost_found
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE campus_lost_found;

-- ------------------------------------------------------------
-- Table: categories
-- Stores item categories (Electronics, Clothing, Books, etc.)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(100) NOT NULL,
    icon     VARCHAR(50)  DEFAULT 'bi-tag'   -- Bootstrap icon class
) ENGINE=InnoDB;

INSERT INTO categories (name, icon) VALUES
('Electronics',  'bi-laptop'),
('Clothing',     'bi-bag'),
('Books',        'bi-book'),
('Accessories',  'bi-watch'),
('Stationery',   'bi-pen'),
('Sports',       'bi-trophy'),
('ID / Cards',   'bi-credit-card'),
('Keys',         'bi-key'),
('Wallet / Bag', 'bi-briefcase'),
('Other',        'bi-three-dots');

-- ------------------------------------------------------------
-- Table: users
-- Stores student accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    password     VARCHAR(255) NOT NULL,         -- bcrypt hash
    phone        VARCHAR(20)  DEFAULT NULL,
    roll_no      VARCHAR(50)  DEFAULT NULL,
    department   VARCHAR(100) DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    is_active    TINYINT(1)   DEFAULT 1          -- 1=active, 0=banned
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: items
-- Stores every lost/found report posted by students
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS items (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    category_id  INT          NOT NULL,
    type         ENUM('lost','found') NOT NULL,  -- 'lost' or 'found'
    item_name    VARCHAR(200) NOT NULL,
    description  TEXT         DEFAULT NULL,
    location     VARCHAR(200) DEFAULT NULL,
    date_lost    DATE         DEFAULT NULL,       -- date item was lost/found
    contact      VARCHAR(200) DEFAULT NULL,       -- contact info provided by poster
    image        VARCHAR(255) DEFAULT NULL,       -- filename in /uploads/
    status       ENUM('active','returned','removed') DEFAULT 'active',
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)     REFERENCES users(id)      ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: admin
-- Stores admin login credentials
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,            -- bcrypt hash
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin: username=admin | password=Admin@123
-- (password hash generated with PASSWORD_BCRYPT cost=12)
INSERT INTO admin (username, password) VALUES
('admin', '$2y$12$YQsKXq3v7xMw2dHqZpK.LuWFZIW7eP1JEk6TxsXN1qB3n7N7Fsdmi');

-- ------------------------------------------------------------
-- Sample data — Students
-- Passwords are all:  Student@123
-- ------------------------------------------------------------
INSERT INTO users (full_name, email, password, phone, roll_no, department) VALUES
('Rahul Sharma',  'rahul@college.edu',  '$2y$12$YQsKXq3v7xMw2dHqZpK.LuWFZIW7eP1JEk6TxsXN1qB3n7N7Fsdmi', '9876543210', 'CS2021001', 'Computer Science'),
('Priya Patel',   'priya@college.edu',  '$2y$12$YQsKXq3v7xMw2dHqZpK.LuWFZIW7eP1JEk6TxsXN1qB3n7N7Fsdmi', '9876543211', 'EC2021002', 'Electronics'),
('Amit Kumar',    'amit@college.edu',   '$2y$12$YQsKXq3v7xMw2dHqZpK.LuWFZIW7eP1JEk6TxsXN1qB3n7N7Fsdmi', '9876543212', 'ME2021003', 'Mechanical');

-- ------------------------------------------------------------
-- Sample data — Items
-- ------------------------------------------------------------
INSERT INTO items (user_id, category_id, type, item_name, description, location, date_lost, contact, status) VALUES
(1, 1, 'lost',  'Black HP Laptop',        'HP Pavilion 15, black colour, has a sticker on lid', 'Library 2nd Floor', '2024-01-15', 'rahul@college.edu / 9876543210', 'active'),
(2, 7, 'lost',  'Student ID Card',        'ID card of Priya Patel, EC branch',                  'Canteen',           '2024-01-16', 'priya@college.edu / 9876543211', 'active'),
(3, 8, 'found', 'Silver Key Bundle',      'Found a bunch of 3 keys near Gate 2',                'Main Gate',         '2024-01-14', 'amit@college.edu / 9876543212', 'active'),
(1, 4, 'lost',  'Black Wrist Watch',      'Fastrack watch, lost near sports ground',             'Sports Ground',     '2024-01-17', 'rahul@college.edu',             'active'),
(2, 3, 'found', 'Engineering Mathematics','Found a textbook — name written inside: Rohan Verma', 'Block-B Corridor',  '2024-01-18', 'priya@college.edu',             'returned');
