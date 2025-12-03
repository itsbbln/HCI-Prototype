<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();
$studentData = $auth->getUserData($user['id'], $user['role']);
$student = $studentData['student'] ?? null;
$attendanceRecords = $studentData['attendance'] ?? [];
$sanctions = $studentData['sanctions'] ?? [];

// Calculate stats
$activeSanctions = array_filter($sanctions, function($sanction) {
    return $sanction['status'] == 'active';
});

$totalClasses = count(array_unique(array_column($attendanceRecords, 'class_id')));
$presentCount = count(array_filter($attendanceRecords, function($record) use ($student) {
    return in_array($student['id'], $record['present_students']);
}));
$attendanceRate = count($attendanceRecords) > 0 ? round(($presentCount / count($attendanceRecords)) * 100) : 0;
?>
            <div class="top-bar">
                <h1>Welcome, <?php echo $user['name']; ?>!</h1>
                <div class="user-info">
                    <div class="notification-icon">
                        🔔
                        <span class="notification-badge">2</span>
                    </div>
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
                        <span class="card-title">Attendance Rate</span>
                        <span class="card-icon">📊</span>
                    </div>
                    <div class="card-value"><?php echo $attendanceRate; ?>%</div>
                    <div class="card-footer">This semester</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Absences</span>
                        <span class="card-icon">🔴</span>
                    </div>
                    <div class="card-value"><?php echo count($attendanceRecords) - $presentCount; ?></div>
                    <div class="card-footer">This semester</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Active Sanctions</span>
                        <span class="card-icon">⚠️</span>
                    </div>
                    <div class="card-value"><?php echo count($activeSanctions); ?></div>
                    <div class="card-footer">Currently active</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Classes</span>
                        <span class="card-icon">📚</span>
                    </div>
                    <div class="card-value"><?php echo $totalClasses; ?></div>
                    <div class="card-footer">Enrolled</div>
                </div>
            </div>

            <div class="quick-actions">
                <a href="attendance.php" class="action-btn">
                    <div class="action-btn-icon">✅</div>
                    <div class="action-btn-text">View Attendance</div>
                </a>
                <a href="sanctions.php" class="action-btn">
                    <div class="action-btn-icon">⚖️</div>
                    <div class="action-btn-text">View Sanctions</div>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-btn-icon">📋</div>
                    <div class="action-btn-text">Class Schedule</div>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-btn-icon">👤</div>
                    <div class="action-btn-text">My Profile</div>
                </a>
            </div>

            <div class="recent-activity">
                <h2 class="section-title">Recent Activity</h2>
                <ul class="activity-list">
                    <?php 
                    $recentAttendance = array_slice(array_reverse($attendanceRecords), 0, 3);
                    foreach ($recentAttendance as $record): 
                        $class = $db->getById('classes', $record['class_id']);
                        $wasPresent = in_array($student['id'], $record['present_students']);
                    ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>Attendance - <?php echo $class['name']; ?></h4>
                            <p><?php echo date('M j, Y', strtotime($record['date'])); ?> • <?php echo $wasPresent ? 'Present' : 'Absent'; ?></p>
                        </div>
                        <span class="activity-time">
                            <span class="attendance-badge <?php echo $wasPresent ? 'present' : 'absent'; ?>">
                                <?php echo $wasPresent ? 'Present' : 'Absent'; ?>
                            </span>
                        </span>
                    </li>
                    <?php endforeach; ?>
                    
                    <?php 
                    $recentSanctions = array_slice(array_reverse($sanctions), 0, 3 - count($recentAttendance));
                    foreach ($recentSanctions as $sanction): 
                    ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>Sanction - <?php echo $sanction['violation_type']; ?></h4>
                            <p><?php echo date('M j, Y', strtotime($sanction['date_issued'])); ?> • <?php echo ucfirst($sanction['sanction_level']); ?> violation</p>
                        </div>
                        <span class="activity-time">
                            <span class="sanction-badge sanction-<?php echo $sanction['sanction_level']; ?>">
                                <?php echo ucfirst($sanction['sanction_level']); ?>
                            </span>
                        </span>
                    </li>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recentAttendance) && empty($recentSanctions)): ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>No recent activity</h4>
                            <p>Your activity will appear here</p>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

    <style>
        .attendance-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .present {
            background: #d1fae5;
            color: #065f46;
        }
        
        .absent {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .sanction-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .sanction-minor {
            background: #fed7aa;
            color: #92400e;
        }
        
        .sanction-major {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .sanction-severe {
            background: #dc2626;
            color: white;
        }
    </style>
<?php require_once '../../includes/footer.php'; ?>