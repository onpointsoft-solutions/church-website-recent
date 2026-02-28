<?php
// Minimal diagnostic script - no dependencies
echo "<h1>Apache Diagnostic</h1>";
echo "<p>Current working directory: " . getcwd() . "</p>";
echo "<p>Document root: " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p>Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

// Check file permissions
$files = ['config.php', 'db.php', '.htaccess'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
        echo "<p>Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

// Check .htaccess
if (file_exists('.htaccess')) {
    echo "<h2>.htaccess contents:</h2>";
    echo "<pre>" . htmlspecialchars(file_get_contents('.htaccess')) . "</pre>";
}
?>
