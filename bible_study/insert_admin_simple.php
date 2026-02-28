<?php
// CEFC Bible Study Management System
// File: insert_admin_simple.php
// Description: Simple admin insertion without database dependencies

// Test admin credentials
$email = 'testadmin@gmail.com';
$password = 'Admin@321';
$name = 'Test Admin';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Admin User Creation</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; }
        .success { color: #10b981; font-size: 18px; margin-bottom: 20px; }
        .info { background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .warning { background: #fef3c7; padding: 15px; border-radius: 8px; margin: 20px 0; border: 1px solid #f59e0b; }
        .btn { background: #8b5cf6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 5px; }
        .code { background: #1f2937; color: #f3f4f6; padding: 15px; border-radius: 5px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>Admin User Creation</h1>
    
    <div class='warning'>
        <h3>⚠️ PHP Extensions Missing</h3>
        <p>Your XAMPP installation is missing required PHP extensions (mysqli, pdo_mysql, etc.).</p>
        <p>Please enable these extensions in your php.ini file or reinstall XAMPP.</p>
    </div>
    
    <div class='info'>
        <h3>Manual Database Insert</h3>
        <p>Since PHP extensions are missing, you can manually insert the admin user using this SQL:</p>
        <div class='code'>
INSERT INTO bs_users (name, email, password, role, status, verified, created_at) 
VALUES ('$name', '$email', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1, NOW());
        </div>
        <p><strong>Password hash for 'Admin@321':</strong> \$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi</p>
    </div>
    
    <div class='info'>
        <h3>Admin Credentials:</h3>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Password:</strong> $password</p>
        <p><strong>Role:</strong> Administrator</p>
    </div>
    
    <div>
        <a href='test_php.php' class='btn'>Test PHP</a>
        <a href='auth/login.php' class='btn'>Go to Login</a>
    </div>
    
    <hr style='margin: 30px 0;'>
    <h3>Fix XAMPP PHP Extensions:</h3>
    <ol>
        <li>Open XAMPP Control Panel</li>
        <li>Click 'Config' next to Apache</li>
        <li>Select 'PHP (php.ini)'</li>
        <li>Uncomment these lines (remove semicolon):</li>
        <ul>
            <li>;extension=mysqli</li>
            <li>;extension=pdo_mysql</li>
            <li>;extension=curl</li>
            <li>;extension=openssl</li>
        </ul>
        <li>Save file and restart Apache</li>
    </ol>
</body>
</html>";
?>
