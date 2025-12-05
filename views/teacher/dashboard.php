<?php
require_once '../../includes/header.php';
checkRole(['teacher']);

// Ensure user is set
if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$user = $_SESSION['user'];
$db = new JsonDB();
$auth = new Auth();
$teacherData = $auth->getUserData($user['id'], $user['role']);

// Get teacher's classes and students
$teacherClasses = $teacherData['classes'];
$allStudents = $teacherData['students'];

// Count total students across all teacher's classes
$studentIds = [];
foreach ($teacherClasses as $class) {
    $studentIds = array_merge($studentIds, $class['students']);
}
$totalStudents = count(array_unique($studentIds));

// Get today's attendance records for teacher's classes
$allAttendance = $db->getAll('attendance');
$todayDate = date('Y-m-d');

$todayAttendance = array_filter($allAttendance, function($record) use ($teacherClasses, $todayDate) {
    $classIds = array_column($teacherClasses, 'id');
    return in_array($record['class_id'], $classIds) && $record['date'] == $todayDate;
});

// Calculate today's present/absent
$presentToday = 0;
$absentToday = 0;
$totalClassesToday = 0;

foreach ($todayAttendance as $record) {
    $class = $db->getById('classes', $record['class_id']);
    if ($class) {
        $presentToday += count($record['present_students']);
        $totalClassesToday += count($class['students']);
    }
}
$absentToday = $totalClassesToday - $presentToday;

// Calculate attendance rate
$attendanceRate = $totalClassesToday > 0 ? round(($presentToday / $totalClassesToday) * 100) : 0;

// Get pending anecdotal reports
$anecdotalRecords = $db->getAll('anecdotal');
$pendingReports = array_filter($anecdotalRecords, function($record) use ($user) {
    return $record['reported_by'] == $user['id'] && $record['status'] == 'pending';
});

// Get recent activity (attendance records and anecdotal reports)
$recentAttendance = array_filter($allAttendance, function($record) use ($teacherClasses, $user) {
    $classIds = array_column($teacherClasses, 'id');
    return in_array($record['class_id'], $classIds) && $record['created_by'] == $user['id'];
});

// Sort by date
usort($recentAttendance, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$recentActivity = array_slice($recentAttendance, 0, 5);
?>
            <div class="top-bar">
                <h1>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <div class="user-info">
                    <a href="<?php echo PathHelper::getRolePath($user['role']); ?>notification.php" class="notification-icon-link">
                        <div class="notification-icon">
                            🔔
                            <?php 
                            $notifications = $db->getAll('notifications');
                            $unreadCount = count(array_filter($notifications, function($n) use ($user) {
                                return $n['user_id'] == $user['id'] && empty($n['is_read']);
                            }));
                            if ($unreadCount > 0):
                            ?>
                            <span class="notification-badge"><?php echo $unreadCount; ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="user-profile-dropdown">
                        <div class="profile-dropdown-toggle">
                            <div class="user-avatar"><?php echo substr($user['name'], 0, 2); ?></div>
                            <div>
                                <div style="font-size: 14px; font-weight: 600;"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div style="font-size: 12px; color: #6e6e6e;">Faculty</div>
                            </div>
                            <span class="dropdown-arrow">▼</span>
                        </div>
                        <div class="profile-dropdown-menu">
                            <div class="profile-dropdown-header">
                                <div class="user-name"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <a href="profile.php" class="profile-dropdown-item">
                                <span class="icon">👤</span>
                                <span>My Profile</span>
                            </a>
                            <a href="settings.php" class="profile-dropdown-item">
                                <span class="icon">⚙️</span>
                                <span>Settings</span>
                            </a>
                            <div class="profile-dropdown-divider"></div>
                            <a href="<?php echo PathHelper::getIncludesPath(); ?>logout.php" class="profile-dropdown-item">
                                <span class="icon">🚪</span>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-cards">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Students</span>
                        <span class="card-icon">👥</span>
                    </div>
                    <div class="card-value"><?php echo $totalStudents; ?></div>
                    <div class="card-footer">Across <?php echo count($teacherClasses); ?> classes</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Present Today</span>
                        <span class="card-icon">🟢</span>
                    </div>
                    <div class="card-value"><?php echo $presentToday; ?></div>
                    <div class="card-footer"><?php echo $attendanceRate; ?>% attendance rate</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Absent Today</span>
                        <span class="card-icon">🔴</span>
                    </div>
                    <div class="card-value"><?php echo $absentToday; ?></div>
                    <div class="card-footer"><?php echo $totalClassesToday > 0 ? round(($absentToday / $totalClassesToday) * 100) : 0; ?>% absence rate</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Reports</span>
                        <span class="card-icon">⚠️</span>
                    </div>
                    <div class="card-value"><?php echo count($pendingReports); ?></div>
                    <div class="card-footer">Awaiting review</div>
                </div>
            </div>

            <h2 class="section-title">Quick Actions</h2>
            <div class="quick-actions">
                <a href="attendance.php" class="action-btn">
                    <div class="action-btn-icon">✅</div>
                    <div class="action-btn-text">Mark Attendance</div>
                </a>
                <a href="anecdotal.php" class="action-btn">
                    <div class="action-btn-icon">📝</div>
                    <div class="action-btn-text">Submit Report</div>
                </a>
                <a href="reports.php" class="action-btn">
                    <div class="action-btn-icon">📊</div>
                    <div class="action-btn-text">View Reports</div>
                </a>
                <a href="classes.php" class="action-btn">
                    <div class="action-btn-icon">👥</div>
                    <div class="action-btn-text">Manage Classes</div>
                </a>
            </div>

            <div class="recent-activity">
                <h2 class="section-title">Recent Activity</h2>
                <ul class="activity-list">
                    <?php if (empty($recentActivity)): ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4>No recent activity</h4>
                                <p>Your activity will appear here</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach ($recentActivity as $activity): 
                            $class = $db->getById('classes', $activity['class_id']);
                            $timeAgo = time() - strtotime($activity['created_at']);
                            if ($timeAgo < 3600) {
                                $timeText = round($timeAgo / 60) . ' minutes ago';
                            } elseif ($timeAgo < 86400) {
                                $timeText = round($timeAgo / 3600) . ' hours ago';
                            } else {
                                $timeText = round($timeAgo / 86400) . ' days ago';
                            }
                        ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4>Attendance marked for <?php echo htmlspecialchars($class['section']); ?></h4>
                                <p><?php echo htmlspecialchars($class['name']); ?> - <?php echo count($activity['present_students']); ?>/<?php echo count($class['students']); ?> present</p>
                            </div>
                            <span class="activity-time"><?php echo $timeText; ?></span>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

<?php require_once '../../includes/footer.php'; ?>