<?php
// CEFC Bible Study Management System
// File: insert_admin.php
// Description: Insert test admin user into database

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once 'config/config.php';
require_once 'config/db.php';

// Test admin credentials
$email = 'testadmin@gmail.com';
$password = 'Admin@321';
$name = 'Test Admin';

try {
    // Check if admin already exists
    $checkStmt = $pdo->prepare("SELECT id FROM bs_users WHERE email = ?");
    $checkStmt->execute([$email]);
    
    if ($checkStmt->fetch()) {
        echo "<h2>Admin Already Exists</h2>";
        echo "<p>The admin user '$email' already exists in the database.</p>";
        echo "<p><a href='auth/login.php'>Go to Login</a></p>";
        exit;
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert admin user
    $insertStmt = $pdo->prepare("
        INSERT INTO bs_users (name, email, password, role, status, verified, created_at) 
        VALUES (?, ?, ?, 'admin', 'active', 1, NOW())
    ");
    
    $result = $insertStmt->execute([$name, $email, $hashedPassword]);
    
    if ($result) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Admin User Created</title>
            <style>
                body { font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; }
                .success { color: #10b981; font-size: 18px; margin-bottom: 20px; }
                .info { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0; }
                .btn { background: #8b5cf6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <h1 class='success'>✅ Admin User Created Successfully!</h1>
            
            <div class='info'>
                <h3>Login Credentials:</h3>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Password:</strong> $password</p>
                <p><strong>Role:</strong> Administrator</p>
            </div>
            
            <p>You can now login with these credentials.</p>
            <a href='auth/login.php' class='btn'>Go to Login</a>
            
            <hr style='margin: 30px 0;'>
            <p style='color: #666; font-size: 12px;'>
                <strong>Security Note:</strong> Please delete this file after creating the admin user.
            </p>
        </body>
        </html>";
    } else {
        throw new Exception("Failed to insert admin user");
    }
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Error creating admin user: " . $e->getMessage() . "</p>";
    echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
}
?>
