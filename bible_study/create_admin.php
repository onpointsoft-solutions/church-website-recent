<?php
// Simple admin creation without complex dependencies
$host = 'localhost';
$dbname = 'bs_cefc';
$username = 'root';
$password = '';

try {
    // Direct database connection
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Check if admin exists
    $email = 'testadmin@gmail.com';
    $check = $conn->query("SELECT id FROM bs_users WHERE email = '$email'");
    
    if ($check->num_rows > 0) {
        echo "<h2>Admin Already Exists</h2>";
        echo "<p>Admin user '$email' already exists.</p>";
        echo "<p><a href='auth/login.php'>Go to Login</a></p>";
        exit;
    }
    
    // Hash password
    $pass = 'Admin@321';
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    
    // Insert admin
    $name = 'Test Admin';
    $sql = "INSERT INTO bs_users (name, email, password, role, status, verified, created_at) 
             VALUES ('$name', '$email', '$hash', 'admin', 'active', 1, NOW())";
    
    if ($conn->query($sql)) {
        echo "<!DOCTYPE html>
        <html>
        <head>
            <title>Admin Created</title>
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
                <p><strong>Password:</strong> $pass</p>
                <p><strong>Role:</strong> Administrator</p>
            </div>
            
            <p>You can now login with these credentials.</p>
            <a href='auth/login.php' class='btn'>Go to Login</a>
        </body>
        </html>";
    } else {
        throw new Exception("Failed to create admin user");
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    echo "<p><a href='javascript:history.back()'>Go Back</a></p>";
}
?>
