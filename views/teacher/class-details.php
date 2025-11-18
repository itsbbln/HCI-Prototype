<?php
require_once '../../includes/header.php';
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$classId = $_GET['id'] ?? null;

if (!$classId) {
    header("Location: classes.php");
    exit();
}

$class = $db->getById('classes', $classId);
if (!$class || $class['teacher_id'] != $user['id']) {
    header("Location: classes.php");
    exit();
}

$students = $db->getAll('students');
$classStudents = array_filter($students, function($student) use ($class) {
    return in_array($student['id'], $class['students']);
});

// Calculate attendance stats
$attendanceRecords = array_filter($db->getAll('attendance'), function($record) use ($classId) {
    return $record['class_id'] == $classId;
});

$studentAttendance = [];
foreach ($classStudents as $student) {
    $presentCount = 0;
    foreach ($attendanceRecords as $record) {
        if (in_array($student['id'], $record['present_students'])) {
            $presentCount++;
        }
    }
    $attendanceRate = count($attendanceRecords) > 0 ? round(($presentCount / count($attendanceRecords)) * 100) : 0;
    $studentAttendance[$student['id']] = [
        'rate' => $attendanceRate,
        'present' => $presentCount,
        'total' => count($attendanceRecords)
    ];
}

$averageAttendance = count($studentAttendance) > 0 ? 
    round(array_sum(array_column($studentAttendance, 'rate')) / count($studentAttendance)) : 0;
?>
            <div class="page-header">
                <h1><?php echo $class['name']; ?> - <?php echo $class['section']; ?></h1>
                <p><?php echo $class['schedule']; ?> • <?php echo $class['room']; ?></p>
            </div>

            <div class="class-header">
                <div class="class-info-grid">
                    <div class="info-card">
                        <h3>Subject</h3>
                        <p><?php echo $class['name']; ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Section</h3>
                        <p><?php echo $class['section']; ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Schedule</h3>
                        <p><?php echo $class['schedule']; ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Room</h3>
                        <p><?php echo $class['room']; ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Total Students</h3>
                        <p><?php echo count($classStudents); ?></p>
                    </div>
                    <div class="info-card">
                        <h3>Average Attendance</h3>
                        <p><?php echo $averageAttendance; ?>%</p>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="attendance.php?class_id=<?php echo $class['id']; ?>" class="btn-view">Take Attendance</a>
                    <a href="reports.php?class_id=<?php echo $class['id']; ?>" class="btn-view" style="background: #10b981;">View Reports</a>
                    <a href="classes.php" class="btn-view" style="background: #f59e0b;">Back to Classes</a>
                </div>
            </div>

            <div class="classes-container">
                <h3 style="margin-bottom: 20px;">Student Roster</h3>
                <table class="classes-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Attendance Rate</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classStudents as $index => $student): 
                            $attendance = $studentAttendance[$student['id']];
                            $statusClass = $attendance['rate'] >= 80 ? 'status-approved' : 
                                         ($attendance['rate'] >= 60 ? 'status-pending' : 'status-rejected');
                            $statusText = $attendance['rate'] >= 80 ? 'Good' : 
                                        ($attendance['rate'] >= 60 ? 'Fair' : 'Poor');
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $student['student_id']; ?></td>
                            <td><?php echo $student['name']; ?></td>
                            <td>
                                <div class="attendance-rate">
                                    <span><?php echo $attendance['rate']; ?>%</span>
                                    <div class="attendance-bar">
                                        <div class="attendance-fill" style="width: <?php echo $attendance['rate']; ?>%; background: <?php echo $attendance['rate'] >= 80 ? '#10b981' : ($attendance['rate'] >= 60 ? '#f59e0b' : '#ef4444'); ?>;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $statusClass; ?>">
                                    <?php echo $statusText; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn-view" onclick="viewStudent(<?php echo $student['id']; ?>)">Profile</button>
                                <a href="anecdotal.php?student_id=<?php echo $student['id']; ?>" class="btn-view" style="background: #f59e0b;">Report</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function viewStudent(studentId) {
            alert('View student profile for ID: ' + studentId + ' - This feature is under development.');
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>