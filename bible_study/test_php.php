<?php
// Simple PHP test to check if basic functionality works
echo "<h1>PHP Test Page</h1>";
echo "<p>PHP is working!</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";

// Test if we can access config files
if (file_exists('config/config.php')) {
    echo "<p style='color: green;'>✅ config.php exists</p>";
} else {
    echo "<p style='color: red;'>❌ config.php missing</p>";
}

if (file_exists('config/db.php')) {
    echo "<p style='color: green;'>✅ db.php exists</p>";
} else {
    echo "<p style='color: red;'>❌ db.php missing</p>";
}
?>
