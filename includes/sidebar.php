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
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => '#']
    ],
    'student' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '✅', 'name' => 'My Attendance', 'link' => 'attendance.php'],
        ['icon' => '⚖️', 'name' => 'My Sanctions', 'link' => 'sanctions.php'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '👤', 'name' => 'Profile', 'link' => '#']
    ],
    'prefect' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '⚖️', 'name' => 'Sanction Management', 'link' => 'sanctions.php'],
        ['icon' => '📝', 'name' => 'Anecdotal Records', 'link' => 'anecdotal.php'],
        ['icon' => '👥', 'name' => 'Student Profiles', 'link' => 'student-profiles.php'],
        ['icon' => '📄', 'name' => 'Reports', 'link' => '#'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => '#']
    ],
    'beadle' => [
        ['icon' => '📊', 'name' => 'Dashboard', 'link' => 'dashboard.php'],
        ['icon' => '✅', 'name' => 'Mark Attendance', 'link' => 'attendance.php'],
        ['icon' => '📋', 'name' => 'Attendance History', 'link' => 'attendance-history.php'],
        ['icon' => '🔔', 'name' => 'Notifications', 'link' => 'notification.php'],
        ['icon' => '⚙️', 'name' => 'Settings', 'link' => '#']
    ]
];
?>
<aside class="sidebar">
    <div class="logo">
        <h2>Smart<span>Discipline</span></h2>
    </div>
    <ul class="nav-menu">
        <?php foreach ($navItems[$role] as $item): 
            $active = ($currentPage == $item['link']) ? 'active' : '';
            $link = PathHelper::getRolePath($role) . $item['link'];
        ?>
            <li><a href="<?php echo $link; ?>" class="<?php echo $active; ?>">
                <span><?php echo $item['icon']; ?></span> <?php echo $item['name']; ?>
            </a></li>
        <?php endforeach; ?>
    </ul>
    <form method="POST" action="<?php echo PathHelper::getIncludesPath(); ?>logout.php">
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</aside>