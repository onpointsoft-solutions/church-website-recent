-- =================================================================
-- Bible Study Management System Database Schema
-- Database: bs_cefc
-- Engine: InnoDB
-- Charset: utf8mb4_unicode_ci
-- Prefix: bs_
-- =================================================================

START TRANSACTION;

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS bs_cefc 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE bs_cefc;

-- =================================================================
-- TABLE 1: bs_users
-- User management table for all system users
-- =================================================================
CREATE TABLE bs_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','coordinator','leader','member') DEFAULT 'member',
    age_group ENUM('youth','young_adult','adult','senior') DEFAULT 'adult',
    group_id INT DEFAULT NULL,
    status ENUM('active','inactive') DEFAULT 'active',
    verified TINYINT(1) DEFAULT 0,
    otp VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_group_id (group_id),
    INDEX idx_age_group (age_group)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 2: bs_semesters
-- Semester management for organizing study periods
-- =================================================================
CREATE TABLE bs_semesters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('upcoming','active','completed') DEFAULT 'upcoming',
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_status (status),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_created_by (created_by),
    
    -- Foreign Key
    FOREIGN KEY (created_by) REFERENCES bs_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 3: bs_groups
-- Study groups within semesters
-- =================================================================
CREATE TABLE bs_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    semester_id INT DEFAULT NULL,
    leader_id INT DEFAULT NULL,
    total_points INT DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_semester_id (semester_id),
    INDEX idx_leader_id (leader_id),
    INDEX idx_status (status),
    INDEX idx_total_points (total_points),
    
    -- Foreign Keys
    FOREIGN KEY (semester_id) REFERENCES bs_semesters(id) ON DELETE SET NULL,
    FOREIGN KEY (leader_id) REFERENCES bs_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 4: bs_sessions
-- Individual study sessions within semesters
-- =================================================================
CREATE TABLE bs_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    session_date DATE NOT NULL,
    session_number INT NOT NULL,
    topic VARCHAR(255) DEFAULT NULL,
    book_reference VARCHAR(100) DEFAULT NULL,
    status ENUM('draft','published') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_semester_id (semester_id),
    INDEX idx_session_date (session_date),
    INDEX idx_session_number (session_number),
    INDEX idx_status (status),
    
    -- Foreign Key
    FOREIGN KEY (semester_id) REFERENCES bs_semesters(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 5: bs_score_categories
-- Scoring categories for sessions
-- =================================================================
CREATE TABLE bs_score_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    max_points INT DEFAULT 3,
    is_custom TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_session_id (session_id),
    INDEX idx_name (name),
    INDEX idx_is_custom (is_custom),
    
    -- Foreign Key
    FOREIGN KEY (session_id) REFERENCES bs_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 6: bs_scores
-- Score records for groups in sessions
-- =================================================================
CREATE TABLE bs_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    group_id INT NOT NULL,
    category_id INT NOT NULL,
    points INT DEFAULT 0,
    entered_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_session_id (session_id),
    INDEX idx_group_id (group_id),
    INDEX idx_category_id (category_id),
    INDEX idx_entered_by (entered_by),
    INDEX idx_points (points),
    
    -- Foreign Keys
    FOREIGN KEY (session_id) REFERENCES bs_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES bs_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES bs_score_categories(id) ON DELETE CASCADE,
    FOREIGN KEY (entered_by) REFERENCES bs_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 7: bs_attendance
-- Attendance tracking for users in sessions
-- =================================================================
CREATE TABLE bs_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    status ENUM('present','late','absent','excused') DEFAULT 'absent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_status (status),
    INDEX idx_unique_attendance (session_id, user_id),
    
    -- Foreign Keys
    FOREIGN KEY (session_id) REFERENCES bs_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES bs_users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES bs_groups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 8: bs_achievements
-- Achievement records for users
-- =================================================================
CREATE TABLE bs_achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT NOT NULL,
    user_id INT NOT NULL,
    group_id INT NOT NULL,
    achievement_type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    points_awarded INT DEFAULT 0,
    recorded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_group_id (group_id),
    INDEX idx_achievement_type (achievement_type),
    INDEX idx_recorded_by (recorded_by),
    INDEX idx_points_awarded (points_awarded),
    
    -- Foreign Keys
    FOREIGN KEY (session_id) REFERENCES bs_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES bs_users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES bs_groups(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES bs_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 9: bs_rewards
-- Reward system for users and groups
-- =================================================================
CREATE TABLE bs_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    group_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    reward_type VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    awarded_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_semester_id (semester_id),
    INDEX idx_group_id (group_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reward_type (reward_type),
    INDEX idx_awarded_date (awarded_date),
    
    -- Foreign Keys
    FOREIGN KEY (semester_id) REFERENCES bs_semesters(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES bs_groups(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES bs_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 10: bs_certificates
-- Certificate management for users
-- =================================================================
CREATE TABLE bs_certificates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    semester_id INT NOT NULL,
    user_id INT NOT NULL,
    certificate_type ENUM('participation','excellence','leadership','memorization'),
    issued_date DATE DEFAULT NULL,
    file_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_semester_id (semester_id),
    INDEX idx_user_id (user_id),
    INDEX idx_certificate_type (certificate_type),
    INDEX idx_issued_date (issued_date),
    INDEX idx_unique_certificate (semester_id, user_id, certificate_type),
    
    -- Foreign Keys
    FOREIGN KEY (semester_id) REFERENCES bs_semesters(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES bs_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- TABLE 11: bs_notifications_log
-- Email notification tracking
-- =================================================================
CREATE TABLE bs_notifications_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    email_address VARCHAR(255) NOT NULL,
    subject VARCHAR(255) DEFAULT NULL,
    status ENUM('sent','failed') DEFAULT 'failed',
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_session_id (session_id),
    INDEX idx_user_id (user_id),
    INDEX idx_email_address (email_address),
    INDEX idx_status (status),
    INDEX idx_sent_at (sent_at),
    
    -- Foreign Keys
    FOREIGN KEY (session_id) REFERENCES bs_sessions(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES bs_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =================================================================
-- AFTER ALL TABLES — Seed Data
-- =================================================================

-- Insert default admin user
-- Password: Admin@2025 (hashed with PASSWORD_BCRYPT)
INSERT INTO bs_users (
    name, 
    email, 
    password, 
    role, 
    status, 
    verified
) VALUES (
    'CEFC Admin',
    'admin@cefc.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'active',
    1
);

COMMIT;

-- =================================================================
-- Schema Creation Complete
-- Database: bs_cefc
-- Total Tables: 11
-- =================================================================