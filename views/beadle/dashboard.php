<?php
require_once '../../includes/header.php';
checkRole(['beadle']);
$user = $_SESSION['user'];

$db = new JsonDB();
$beadleData = $auth->getUserData($user['id'], $user['role']);

// Get today's class and stats
$todayClass = $beadleData['classes'][0] ?? null; // First class for demo
$totalStudents = count($todayClass['students'] ?? []);
$todayAttendance = array_filter($beadleData['attendance'], function($record) {
    return $record['date'] == date('Y-m-d');
});
$pendingTasks = empty($todayAttendance) ? 1 : 0;
?>
            <div class="top-bar">
                <h1>Welcome, <?php echo $user['name']; ?>!</h1>
                <div class="user-info">
                    <div class="notification-icon">
                        🔔
                        <span class="notification-badge">2</span>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo substr($user['name'], 0, 2); ?></div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;"><?php echo $user['name']; ?></div>
                            <div style="font-size: 12px; color: #6e6e6e;">Grade 10-A</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-cards">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Today's Class</span>
                        <span class="card-icon">📚</span>
                    </div>
                    <div class="card-value"><?php echo $todayClass['name'] ?? 'No Class'; ?></div>
                    <div class="card-footer"><?php echo $todayClass['schedule'] ?? ''; ?></div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Students</span>
                        <span class="card-icon">👥</span>
                    </div>
                    <div class="card-value"><?php echo $totalStudents; ?></div>
                    <div class="card-footer">In your class</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Pending Tasks</span>
                        <span class="card-icon">📝</span>
                    </div>
                    <div class="card-value"><?php echo $pendingTasks; ?></div>
                    <div class="card-footer">Attendance to submit</div>
                </div>
            </div>

            <h2 class="section-title">Quick Actions</h2>
            <div class="quick-actions">
                <a href="attendance.php" class="action-btn">
                    <div class="action-btn-icon">✅</div>
                    <div class="action-btn-text">Mark Today's Attendance</div>
                </a>
                <a href="attendance-history.php" class="action-btn">
                    <div class="action-btn-icon">📋</div>
                    <div class="action-btn-text">View Attendance History</div>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-btn-icon">👥</div>
                    <div class="action-btn-text">Class Roster</div>
                </a>
            </div>

            <div class="recent-activity">
                <h2 class="section-title">Recent Submissions</h2>
                <ul class="activity-list" id="recentSubmissions">
                    <?php if (empty($beadleData['attendance'])): ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4>No submissions yet</h4>
                                <p>Mark attendance to see history</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php 
                        $recentSubmissions = array_slice(array_reverse($beadleData['attendance']), 0, 2);
                        foreach ($recentSubmissions as $submission): 
                            $class = $db->getById('classes', $submission['class_id']);
                        ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4>Attendance submitted for <?php echo $class['name']; ?></h4>
                                <p><?php echo count($submission['present_students']); ?>/<?php echo count($class['students']); ?> present - <?php echo ucfirst($submission['status']); ?></p>
                            </div>
                            <span class="activity-time"><?php echo date('M j, Y', strtotime($submission['date'])); ?></span>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </main>
    </div>
<?php require_once '../../includes/footer.php'; ?>