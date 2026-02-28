<?php
// Minimal PHP test - no database connections
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Debug Test</h1>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max execution time: " . ini_get('max_execution_time') . "</p>";

// Test basic functions
echo "<h2>Basic Function Tests:</h2>";
echo "<p>✅ echo works</p>";

if (function_exists('password_hash')) {
    echo "<p>✅ password_hash available</p>";
} else {
    echo "<p>❌ password_hash NOT available</p>";
}

if (extension_loaded('mysqli')) {
    echo "<p>✅ mysqli extension loaded</p>";
} else {
    echo "<p>❌ mysqli extension NOT loaded</p>";
}

if (extension_loaded('pdo')) {
    echo "<p>✅ PDO extension loaded</p>";
} else {
    echo "<p>❌ PDO extension NOT loaded</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p>✅ PDO MySQL extension loaded</p>";
} else {
    echo "<p>❌ PDO MySQL extension NOT loaded</p>";
}

// Test file includes
echo "<h2>File Include Tests:</h2>";
if (file_exists('config/config.php')) {
    echo "<p>✅ config.php exists</p>";
    try {
        include_once 'config/config.php';
        echo "<p>✅ config.php included successfully</p>";
        if (defined('APP_NAME')) {
            echo "<p>✅ Constants defined: " . APP_NAME . "</p>";
        }
    } catch (Error $e) {
        echo "<p>❌ config.php include error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p>❌ config.php missing</p>";
}
?>
