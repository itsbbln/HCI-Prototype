<?php
require_once '../../includes/header.php';
checkRole(['teacher']);

$db = new JsonDB();
$userId = $_SESSION['user']['id'];
$classId = $_GET['id'] ?? null;

if (!$classId || !is_numeric($classId)) {
    header("Location: classes.php");
    exit();
}

$class = $db->getById('classes', (int)$classId);
if (!$class || $class['teacher_id'] != $userId) {
    echo "<h2>Unauthorized Access</h2>";
    require_once '../../includes/footer.php';
    exit();
}

$success = $error = '';

// === ADD STUDENTS TO CLASS ===
if ($_POST && isset($_POST['add_students'])) {
    $selected = $_POST['student_ids'] ?? [];
    $current = $class['students'] ?? [];
    $updated = array_unique(array_merge($current, $selected));

    if ($db->update('classes', $class['id'], ['students' => $updated])) {
        $success = count($selected) . " student(s) added successfully!";
        $class['students'] = $updated; // Refresh class data
    } else {
        $error = "Failed to add students.";
    }
}

// Load data
$allStudents = $db->getAll('students');
$enrolledIds = $class['students'] ?? [];
$classStudents = array_filter($allStudents, fn($s) => in_array($s['id'], $enrolledIds));
$availableStudents = array_filter($allStudents, fn($s) => !in_array($s['id'], $enrolledIds));

// Attendance stats
$attendanceRecords = array_filter($db->getAll('attendance'), fn($r) => $r['class_id'] == $classId);
$studentAttendance = [];

foreach ($classStudents as $student) {
    $presentCount = 0;
    foreach ($attendanceRecords as $record) {
        if (in_array($student['id'], $record['present_students'] ?? [])) {
            $presentCount++;
        }
    }
    $total = count($attendanceRecords);
    $rate = $total > 0 ? round(($presentCount / $total) * 100) : 0;
    $studentAttendance[$student['id']] = ['rate' => $rate, 'present' => $presentCount, 'total' => $total];
}

$averageAttendance = !empty($studentAttendance) ? 
    round(array_sum(array_column($studentAttendance, 'rate')) / count($studentAttendance)) : 0;
?>

<div class="top-bar page-header">
    <h1><?= htmlspecialchars($class['name']) ?> - <?= htmlspecialchars($class['section']) ?></h1>
    <p><?= htmlspecialchars($class['schedule']) ?> • <?= htmlspecialchars($class['room']) ?></p>
</div>

<?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
<?php endif; ?>

<div class="class-info-grid">
    <div class="info-card">
        <h3>Total Students</h3>
        <p><?= count($classStudents) ?></p>
    </div>
    <div class="info-card">
        <h3>Average Attendance</h3>
        <p><?= $averageAttendance ?>%</p>
    </div>
    <div class="info-card">
        <h3>Available to Add</h3>
        <p><?= count($availableStudents) ?></p>
    </div>
</div>

<div class="action-buttons">
    <a href="attendance.php?class_id=<?= $class['id'] ?>" class="btn-view">Take Attendance</a>
    <a href="reports.php?class_id=<?= $class['id'] ?>" class="btn-view" style="background:#10b981;">View Reports</a>
    <button onclick="openModal('addStudentsModal')" class="btn-submit">+ Add Students</button>
    <a href="classes.php" class="btn-view" style="background:#6b7280;">Back</a>
</div>

<!-- Add Students Modal -->
<div id="addStudentsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="close" onclick="closeModal('addStudentsModal')">&times;</span>
        <h3>Add Students to Class</h3>
        <form method="POST">
            <p>Select students to enroll:</p>
            <select name="student_ids[]" multiple size="15" style="width:100%; padding:10px; font-size:14px;" required>
                <?php foreach ($availableStudents as $student): ?>
                    <option value="<?= $student['id'] ?>">
                        <?= $student['student_id'] ?> - <?= htmlspecialchars($student['name']) ?>
                        <?= !empty($student['grade']) ? " ({$student['grade']})" : '' ?>
                    </option>
                <?php endforeach; ?>
                <?php if (empty($availableStudents)): ?>
                    <option disabled>All students already enrolled</option>
                <?php endif; ?>
            </select>
            <br><br>
            <button type="submit" name="add_students" class="btn-submit" <?= empty($availableStudents) ? 'disabled' : '' ?>>
                Add Selected Students
            </button>
        </form>
    </div>
</div>

<!-- Student Roster -->
<div class="classes-container" style="margin-top: 40px;">
    <h3>Student Roster</h3>
    <?php if (empty($classStudents)): ?>
        <p style="text-align:center; color:#666; padding:40px;">No students enrolled yet. Click "+ Add Students" above.</p>
    <?php else: ?>
    <table class="classes-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Student ID</th>
                <th>Name</th>
                <th>Attendance</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classStudents as $index => $student): 
                $att = $studentAttendance[$student['id']] ?? ['rate' => 0];
                $rate = $att['rate'];
                $statusClass = $rate >= 80 ? 'status-approved' : ($rate >= 60 ? 'status-pending' : 'status-rejected');
                $statusText = $rate >= 80 ? 'Good' : ($rate >= 60 ? 'Fair' : 'Needs Improvement');
            ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= $student['student_id'] ?></td>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td>
                    <div class="attendance-rate">
                        <span><?= $rate ?>%</span>
                        <div class="attendance-bar">
                            <div class="attendance-fill" style="width:<?= $rate ?>%; background:<?= $rate >= 80 ? '#10b981' : ($rate >= 60 ? '#f59e0b' : '#ef4444') ?>"></div>
                        </div>
                    </div>
                </td>
                <td><span class="status-badge <?= $statusClass ?>"><?= $statusText ?></span></td>
                <td>
                    <a href="anecdotal.php?student_id=<?= $student['id'] ?>&class_id=<?= $class['id'] ?>" 
                       class="btn-view" style="background:#f59e0b; font-size:12px;">Report Issue</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>