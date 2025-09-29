-- Portfolio Database Schema
-- Run this in your MySQL database (phpMyAdmin or MySQL command line)

CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

-- Contact form submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(100),
    message TEXT NOT NULL,
    resume_filename VARCHAR(255),
    resume_path VARCHAR(500),
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    INDEX idx_email (email),
    INDEX idx_created_at (created_at),
    INDEX idx_status (status)
);

-- Analytics tracking table
CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(50) NOT NULL,
    event_category VARCHAR(50),
    event_label VARCHAR(100),
    event_value VARCHAR(100),
    page_url VARCHAR(500),
    referrer VARCHAR(500),
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at),
    INDEX idx_session_id (session_id)
);

-- Page views tracking
CREATE TABLE IF NOT EXISTS page_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    page_url VARCHAR(500) NOT NULL,
    page_title VARCHAR(200),
    referrer VARCHAR(500),
    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(100),
    view_duration INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_page_url (page_url),
    INDEX idx_created_at (created_at),
    INDEX idx_session_id (session_id)
);

-- Newsletter subscriptions (if you want to add this feature)
CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    status ENUM('active', 'unsubscribed') DEFAULT 'active',
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    ip_address VARCHAR(45),
    INDEX idx_email (email),
    INDEX idx_status (status)
);

-- Security logs for monitoring
CREATE TABLE IF NOT EXISTS security_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type ENUM('failed_login', 'suspicious_activity', 'file_upload', 'form_submission') NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_event_type (event_type),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address)
);

-- Create a user for the application (optional, for better security)
-- CREATE USER 'portfolio_user'@'localhost' IDENTIFIED BY 'your_secure_password';
-- GRANT SELECT, INSERT, UPDATE ON portfolio_db.* TO 'portfolio_user'@'localhost';
-- FLUSH PRIVILEGES;
