-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS logistics_hub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'logistics_user'@'%' IDENTIFIED BY 'logistics_password';
GRANT ALL PRIVILEGES ON logistics_hub.* TO 'logistics_user'@'%';
FLUSH PRIVILEGES;