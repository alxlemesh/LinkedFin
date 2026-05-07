-- LinkedFin database schema
-- Run setup_db.php to create the database and seed the default user,
-- or execute this file directly against an existing database.

CREATE DATABASE IF NOT EXISTS linkedfin
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE linkedfin;

CREATE TABLE IF NOT EXISTS users (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(50)  UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name         VARCHAR(100) NOT NULL DEFAULT '',
    headline     VARCHAR(220) NOT NULL DEFAULT '',
    location     VARCHAR(100) NOT NULL DEFAULT '',
    bio          VARCHAR(2000) NOT NULL DEFAULT '',
    connections  INT NOT NULL DEFAULT 0,
    avatar       VARCHAR(255) DEFAULT NULL,
    banner       VARCHAR(255) DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    content    VARCHAR(3000) NOT NULL,
    likes      INT NOT NULL DEFAULT 0,
    comments   INT NOT NULL DEFAULT 0,
    shares     INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default user  (username: root, password: lbhtrnjh)
-- The password_hash is generated at run time by setup_db.php.
