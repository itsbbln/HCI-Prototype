<?php
require_once '../../includes/header.php';
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$teacherData = $auth->getUserData($user['id'], $user['role']);

// Calculate stats
$totalStudents = count($teacherData['students']);
$teacherClasses = $teacherData['classes'];
$totalClasses = count($teacherClasses);
?>
            <div class="top-bar">
            <h1>Welcome, <span class="welcome-name"><?php echo $user['name']; ?></span>!</h1>
            <div class="user-info">
                <!-- <div class="notification-icon">
                    🔔
                    <span class="notification-badge">2</span>
                    <a href="notification.php" class="notification-icon"></a>>
                </div> -->
                <a href="notification.php" class="notification-icon" aria-label="Notifications">
                    <span class="notif-emoji">🔔</span>
                    <span class="notification-badge">2</span>
                </a>
                <div class="user-profile-dropdown">
                    <div class="profile-dropdown-toggle">
                        <div class="user-avatar"><?php echo substr($user['name'], 0, 2); ?></div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;"><?php echo $user['name']; ?></div>
                            <div style="font-size: 12px; color: #6e6e6e;">
                                <?php echo ucfirst($user['role']); ?>
                            </div>
                        </div>
                        <span class="dropdown-arrow">▼</span>
                    </div>
                    <div class="profile-dropdown-menu">
                        <div class="profile-dropdown-header">
                            <div class="user-name"><?php echo $user['name']; ?></div>
                            <div class="user-email"><?php echo $user['email']; ?></div>
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
                    <div class="card-footer">Across <?php echo $totalClasses; ?> classes</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Present Today</span>
                        <span class="card-icon">🟢</span>
                    </div>
                    <div class="card-value">142</div>
                    <div class="card-footer">91% attendance rate</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Absent Today</span>
                        <span class="card-icon">🔴</span>
                    </div>
                    <div class="card-value">14</div>
                    <div class="card-footer">9% absence rate</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Reports</span>
                        <span class="card-icon">⚠️</span>
                    </div>
                    <div class="card-value">3</div>
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
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>Attendance marked for Grade 10-A</h4>
                            <p>English subject - 28/30 present</p>
                        </div>
                        <span class="activity-time">2 hours ago</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>Anecdotal report submitted</h4>
                            <p>Student: Juan Dela Cruz - Late arrival</p>
                        </div>
                        <span class="activity-time">5 hours ago</span>
                    </li>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>Monthly report generated</h4>
                            <p>October 2024 attendance summary</p>
                        </div>
                        <span class="activity-time">1 day ago</span>
                    </li>
                </ul>
            </div>

<?php require_once '../../includes/footer.php'; ?>