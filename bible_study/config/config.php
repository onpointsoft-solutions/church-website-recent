<?php
// CEFC Bible Study Management System
// File: config.php
// Description: Main configuration file for Bible Study Management System

// App Info
define('APP_NAME', 'CEFC Bible Study');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost/church-website-recent/bible_study');

// Database - XAMPP local settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'bs_cefc');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Session
define('SESSION_TIMEOUT', 3600);
define('SESSION_NAME', 'bs_cefc_session');

// Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_COORDINATOR', 'coordinator');
define('ROLE_LEADER', 'leader');
define('ROLE_MEMBER', 'member');

// Email (PHPMailer - Gmail SMTP)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'bonniecomputerhub24@gmail.com');  // Replace with your Gmail
define('MAIL_PASSWORD', 'lvfb eppn khbd nixs');     // Gmail App Password (16 chars)
define('MAIL_FROM_NAME', 'CEFC Bible Study');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('CERT_PATH', ROOT_PATH . '/certificates/generated/');
define('UPLOAD_PATH', ROOT_PATH . '/uploads/');

// Vendor path pointing to bible_study vendor folder
define('VENDOR_PATH', ROOT_PATH . '/vendor/autoload.php');

// Scoring constants
define('SCORE_EXCELLENT', 3);
define('SCORE_STANDARD', 1);
define('SCORE_NOT_ACHIEVED', 0);
define('MAX_GROUP_SIZE', 6);

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}