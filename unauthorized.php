<?php
session_start();
require_once 'config/path_helper.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - Attendance System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo PathHelper::getCssPath(); ?>styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="left">
            <h1>Access <br><span>Denied.</span></h1>
            <p>You don't have permission to access this page.</p>
        </div>

        <div class="login-card" style="text-align: center;">
            <h2>Unauthorized Access</h2>
            <p style="margin-bottom: 20px;">You don't have the required permissions to view this page.</p>
            <a href="login.php" class="btn-view" style="display: inline-block; padding: 10px 20px; text-decoration: none;">Return to Login</a>
            <?php if (isset($_SESSION['user'])): ?>
            <a href="<?php echo PathHelper::getRolePath(); ?>dashboard.php" class="btn-view" style="display: inline-block; padding: 10px 20px; text-decoration: none; background: #6b7280; margin-left: 10px;">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>