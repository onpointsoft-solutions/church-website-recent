<?php
// CEFC Bible Study Management System
// File: db.php
// Description: Database connection handler using PDO

require_once __DIR__ . '/config.php';

try {
    // Create PDO connection using constants from config.php
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    // Log error to file with timestamp
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Database connection failed: " . $e->getMessage() . PHP_EOL;
    $logFile = ROOT_PATH . '/logs/db_errors.log';
    
    // Create logs directory if it doesn't exist
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    // Write error to log file
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    
    // Show friendly message to user
    die("System temporarily unavailable.");
}