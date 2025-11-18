<?php
// Simple test to check if files exist
$files = [
    'config/session.php',
    'config/json_db.php',
    'includes/auth.php',
    'includes/header.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✅ $file exists<br>";
    } else {
        echo "❌ $file is missing<br>";
    }
}

// Test JSON DB
require_once 'config/json_db.php';
$db = new JsonDB();
echo "✅ JSON DB initialized<br>";

// Test session
require_once 'config/session.php';
echo "✅ Session started<br>";
?>