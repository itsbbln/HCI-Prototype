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
    <style>
        .user-profile-dropdown {
            position: relative;
        }
        
        .profile-dropdown-toggle {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background 0.3s;
        }
        
        .profile-dropdown-toggle:hover {
            background: #f3f4f6;
        }
        
        .profile-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            min-width: 220px;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }
        
        .profile-dropdown-menu.show {
            display: block;
        }
        
        .profile-dropdown-header {
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }
        
        .profile-dropdown-header .user-name {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }
        
        .profile-dropdown-header .user-email {
            font-size: 12px;
            color: #6b7280;
            margin-top: 2px;
        }
        
        .profile-dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #374151;
            text-decoration: none;
            transition: background 0.2s;
            font-size: 14px;
        }
        
        .profile-dropdown-item:hover {
            background: #f3f4f6;
        }
        
        .profile-dropdown-item .icon {
            font-size: 18px;
        }
        
        .profile-dropdown-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 4px 0;
        }
        
        .dropdown-arrow {
            transition: transform 0.3s;
            font-size: 12px;
        }
        
        .dropdown-arrow.open {
            transform: rotate(180deg);
        }
    </style>
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
    
    <script>
        // Profile dropdown toggle
        document.addEventListener('DOMContentLoaded', function() {
            const profileToggle = document.querySelector('.profile-dropdown-toggle');
            const profileMenu = document.querySelector('.profile-dropdown-menu');
            const dropdownArrow = document.querySelector('.dropdown-arrow');
            
            if (profileToggle && profileMenu) {
                profileToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileMenu.classList.toggle('show');
                    dropdownArrow?.classList.toggle('open');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                        profileMenu.classList.remove('show');
                        dropdownArrow?.classList.remove('open');
                    }
                });
            }
        });
    </script>
</body>
</html>