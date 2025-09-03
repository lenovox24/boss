<?php
// File: debug.php - Untuk debugging deployment issues
// HAPUS FILE INI SETELAH DEBUG SELESAI

echo "<h1>Debug Information</h1>";

// 1. PHP Version
echo "<h2>PHP Version</h2>";
echo "PHP Version: " . phpversion() . "<br>";

// 2. Server Information
echo "<h2>Server Information</h2>";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Not set') . "<br>";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "<br>";

// 3. File Permissions
echo "<h2>File Permissions</h2>";
$files_to_check = [
    'index.php',
    'includes/db_connect.php',
    'includes/header.php',
    'config.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "$file: " . substr(sprintf('%o', fileperms($file)), -4) . " (exists)<br>";
    } else {
        echo "$file: NOT FOUND<br>";
    }
}

// 4. Database Connection Test
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'includes/db_connect.php';
    echo "Database connection: SUCCESS<br>";

    // Test query
    $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE()");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Tables in database: " . $row['count'] . "<br>";
    }
} catch (Exception $e) {
    echo "Database connection: FAILED - " . $e->getMessage() . "<br>";
}

// 5. PHP Extensions
echo "<h2>Required PHP Extensions</h2>";
$required_extensions = ['mysqli', 'session', 'json', 'mbstring'];
foreach ($required_extensions as $ext) {
    echo "$ext: " . (extension_loaded($ext) ? 'LOADED' : 'NOT LOADED') . "<br>";
}

// 6. Error Reporting
echo "<h2>Error Reporting</h2>";
echo "Display Errors: " . (ini_get('display_errors') ? 'ON' : 'OFF') . "<br>";
echo "Error Reporting: " . error_reporting() . "<br>";
echo "Log Errors: " . (ini_get('log_errors') ? 'ON' : 'OFF') . "<br>";

// 7. Session Test
echo "<h2>Session Test</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Session Status: " . session_status() . "<br>";

// 8. Directory Structure
echo "<h2>Directory Structure</h2>";
function listDir($dir, $level = 0)
{
    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
    if (is_dir($dir)) {
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..' && $file != '.git') {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    echo $indent . "📁 $file/<br>";
                    listDir($path, $level + 1);
                } else {
                    echo $indent . "📄 $file<br>";
                }
            }
        }
    }
}

listDir('.');

echo "<hr>";
echo "<p><strong>Note:</strong> Hapus file debug.php ini setelah selesai debugging untuk keamanan.</p>";
