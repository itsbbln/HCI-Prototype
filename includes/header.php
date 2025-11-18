<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/json_db.php';
require_once __DIR__ . '/../config/path_helper.php';
require_once __DIR__ . '/auth.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$auth = new Auth();

$basePath = PathHelper::getBasePath();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance & Sanction System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo PathHelper::getCssPath(); ?>styles.css">
    <link rel="stylesheet" href="<?php echo PathHelper::getCssPath(); ?>td.css">
    <?php 
    if ($user) {
        echo '<link rel="stylesheet" href="' . PathHelper::getCssPath() . 'td.css">';
    }
    ?>
</head>
<body>
    <?php if ($user): ?>
    <div class="dashboard-container">
        <?php 
        // Use direct file path for includes
        $sidebarPath = __DIR__ . '/sidebar.php';
        if (file_exists($sidebarPath)) {
            include $sidebarPath;
        } else {
            // Fallback path
            $sidebarPath = dirname(__DIR__) . '/includes/sidebar.php';
            if (file_exists($sidebarPath)) {
                include $sidebarPath;
            }
        }
        ?>
        <main class="main-content">
    <?php endif; ?>