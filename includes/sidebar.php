<?php
require_once __DIR__ . '/../config/path_helper.php';

$user = $_SESSION['user'];
$role = $user['role'];
$currentPage = PathHelper::getCurrentPage();

$navItems = [
    'teacher' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '✅', 'name' => 'Attendance', 'link' => 'attendance.php'],
        ['icon' => '👥', 'name' => 'My Classes', 'link' => 'classes.php'],
        ['icon' => '📄', 'name' => 'Reports', 'link' => 'reports.php'],
        ['icon' => '📝', 'name' => 'Anecdotal Records', 'link' => 'anecdotal.php'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '👤', 'name' => 'Profile', 'link' => 'profile.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => 'settings.php']
    ],
    'student' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '✅', 'name' => 'My Attendance', 'link' => 'attendance.php'],
        ['icon' => '⚖️', 'name' => 'My Sanctions', 'link' => 'sanctions.php'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '👤', 'name' => 'Profile', 'link' => 'profile.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => 'settings.php']
    ],
    'prefect' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '⚖️', 'name' => 'Sanction Management', 'link' => 'sanctions.php'],
        ['icon' => '📝', 'name' => 'Anecdotal Records', 'link' => 'anecdotal.php'],
        ['icon' => '👥', 'name' => 'Student Profiles', 'link' => 'student-profiles.php'],
        ['icon' => '📄', 'name' => 'Reports', 'link' => '#'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '👤', 'name' => 'Profile', 'link' => 'profile.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => 'settings.php']
    ],
    'beadle' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '✅', 'name' => 'Mark Attendance', 'link' => 'attendance.php'],
        ['icon' => '📋', 'name' => 'Attendance History', 'link' => 'attendance-history.php'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '👤', 'name' => 'Profile', 'link' => 'profile.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => 'settings.php']
    ],
    'admin' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '✅', 'name' => 'Classes', 'link' => 'classes.php'],
        ['icon' => '📋', 'name' => 'Manage Users', 'link' => 'users.php'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '👥', 'name' => 'Manage Prefect', 'link' => 'prefects.php'],
        ['icon' => '👤', 'name' => 'Profile', 'link' => 'profile.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => 'settings.php']
    ]
];
?>

<!-- Hamburger Menu Button (Always Visible) -->
<button class="menu-toggle" id="menuToggle">
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
    <span class="hamburger-line"></span>
</button>

<!-- Overlay (Dark background when drawer is open) -->
<div class="drawer-overlay" id="drawerOverlay"></div>

<!-- Drawer Sidebar -->
<aside class="drawer-sidebar" id="drawerSidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h2><span class="logo-text">DNHS-</span><span class="logo-accent">SMS</span></h2>
        </div>
    </div>
    
    <ul class="nav-menu">
        <?php foreach ($navItems[$role] as $item): 
            $active = ($currentPage == $item['link']) ? 'active' : '';
            $link = PathHelper::getRolePath($role) . $item['link'];
        ?>
            <li>
                <a href="<?php echo $link; ?>" class="nav-link <?php echo $active; ?>">
                    <span class="nav-icon"><?php echo $item['icon']; ?></span>
                    <span class="nav-text"><?php echo $item['name']; ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    
    <div class="sidebar-footer">
        <form method="POST" action="<?php echo PathHelper::getIncludesPath(); ?>logout.php">
            <button type="submit" class="logout-btn">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </button>
        </form>
    </div>
</aside>

<style>
    /* Hamburger Menu Button */
    .menu-toggle {
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1002;
        width: 46px;
        height: 46px;
        background: #2c55f0;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 6px;
        padding: 10px;
        box-shadow: 0 4px 12px rgba(44, 85, 240, 0.3);
        transition: all 0.3s ease;
    }

    .menu-toggle:hover {
        background: #1e40af;
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(44, 85, 240, 0.4);
    }

    .hamburger-line {
        width: 24px;
        height: 3px;
        background: white;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    /* Animate to X when drawer is open */
    .menu-toggle.active .hamburger-line:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }

    .menu-toggle.active .hamburger-line:nth-child(2) {
        opacity: 0;
    }

    .menu-toggle.active .hamburger-line:nth-child(3) {
        transform: rotate(-45deg) translate(8px, -8px);
    }

    /* Overlay */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .drawer-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Drawer Sidebar */
    .drawer-sidebar {
        width: 280px;
        background: linear-gradient(180deg, #dfe4f3ff 0%, #f7f7f7ff 100%);
        color: white;
        position: fixed;
        height: 100vh;
        left: 0;
        top: 0;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        display: flex;
        flex-direction: column;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.3);
    }

    .drawer-sidebar.open {
        transform: translateX(0);
    }

    .sidebar-header {
        padding: 30px 20px 20px 70px;
        border-bottom: 1px solid #334155;
    }
    
    /* Adjust button position when drawer is open */
    .drawer-sidebar.open ~ .menu-toggle {
        left: 240px;
    }

    .logo {
        max-width: 100%;
        overflow: hidden;
    }

    .logo h2 {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0;
        white-space: nowrap;
    }

    .logo-text {
        color: white;
    }

    .logo-accent {
        color: #2c55f0;
    }

    .nav-menu {
        list-style: none;
        padding: 20x 0;
        margin: 0;
        flex: 1;
        overflow-y: auto;
    }

    .nav-menu::-webkit-scrollbar {
        width: 6px;
    }

    .nav-menu::-webkit-scrollbar-track {
        background: transparent;
    }

    .nav-menu::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .nav-menu li {
        margin: 5px 15px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: #cbd5e1;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
        font-size: 14px;
    }

    .nav-link:hover {
        background: rgba(44, 85, 240, 0.1);
        color: white;
    }

    .nav-link.active {
        background: #2c55f0;
        color: white;
    }

    .nav-icon {
        font-size: 1.2rem;
        min-width: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .nav-text {
        margin-left: 12px;
        flex: 1;
    }

    .sidebar-footer {
        padding: 20px;
        border-top: 1px solid #ffffffff;
    }

    .logout-btn {
        width: 80%;
        padding: 12px;
        background: #dc2626;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
    }

    .logout-btn:hover {
        background: #b91c1c;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(220, 38, 38, 0.3);
    }

    /* Main content - no margin since drawer is overlay */
    .main-content {
        margin-left: 0;
        width: 100%;
        padding-top: 80px; /* Space for the hamburger button */
    }

    /* Prevent body scroll when drawer is open */
    body.drawer-open {
        overflow: hidden;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const drawerSidebar = document.getElementById('drawerSidebar');
        const drawerOverlay = document.getElementById('drawerOverlay');
        const body = document.body;

        function openDrawer() {
            drawerSidebar.classList.add('open');
            drawerOverlay.classList.add('active');
            menuToggle.classList.add('active');
            body.classList.add('drawer-open');
        }

        function closeDrawer() {
            drawerSidebar.classList.remove('open');
            drawerOverlay.classList.remove('active');
            menuToggle.classList.remove('active');
            body.classList.remove('drawer-open');
        }

        // Toggle drawer
        menuToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            if (drawerSidebar.classList.contains('open')) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        // Close drawer when clicking overlay
        drawerOverlay.addEventListener('click', closeDrawer);

        // Close drawer when clicking a link
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                closeDrawer();
            });
        });

        // Close drawer on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && drawerSidebar.classList.contains('open')) {
                closeDrawer();
            }
        });
    });
</script>