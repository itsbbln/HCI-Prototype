<?php
require_once '../../includes/header.php';
checkRole(['beadle']);
$user = $_SESSION['user'];

$db = new JsonDB();
$auth = new Auth();
$beadleData = $auth->getUserData($user['id'], $user['role']);

$todayClass = $beadleData['classes'][0] ?? null;

if ($_POST) {
    $classId = $_POST['class_id'];
    $date = $_POST['date'];
    $presentStudents = $_POST['present_students'] ?? [];
    
    $attendanceData = [
        'class_id' => $classId,
        'date' => $date,
        'created_by' => $user['id'],
        'present_students' => $presentStudents,
        'status' => 'pending'
    ];
    
    if ($db->insert('attendance', $attendanceData)) {
        $success = "Attendance submitted successfully for " . count($presentStudents) . " students! Waiting for teacher approval.";
    } else {
        $error = "Failed to submit attendance. Please try again.";
    }
}
<?php
// Beadle role removed. Redirect to unauthorized.
header('Location: ../../unauthorized.php');
exit;
<?php
// Beadle role removed. Redirect to unauthorized.
header('Location: ../../unauthorized.php');
exit;
?>

            <?php if ($todayClass): ?>
            <div class="attendance-sheet-container">
                <div class="sheet-header">
                    <h2>Attendance for Today - <?php echo $todayClass['name']; ?></h2>
                    <p><?php echo date('F j, Y'); ?> • <?php echo $todayClass['section']; ?> • <?php echo $todayClass['room']; ?></p>
                </div>

                <form method="POST">
                    <input type="hidden" name="class_id" value="<?php echo $todayClass['id']; ?>">
                    <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
                    
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllStudents(this)">
                                </th>
                                <th>#</th>
                                <th>Student Name</th>
                                <th>Student ID</th>
                                <th>Present</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $students = $db->getAll('students');
                            $classStudents = array_filter($students, function($student) use ($todayClass) {
                                return in_array($student['id'], $todayClass['students']);
                            });
                            
                            foreach ($classStudents as $index => $student): ?>
                            <tr>
                                <td class="checkbox-cell">
                                    <input type="checkbox" class="student-checkbox" 
                                           name="present_students[]" value="<?php echo $student['id']; ?>" checked>
                                </td>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['student_id']; ?></td>
                                <td>
                                    <span class="status-text">Marked Present</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn-submit">Submit Attendance</button>
                    </div>
                </form>
            </div>
            <?php else: ?>
                <div style="background: #fef3c7; color: #92400e; padding: 20px; border-radius: 8px; text-align: center;">
                    <h3>No Class Assigned</h3>
                    <p>You don't have any classes assigned to you. Please contact administration.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function toggleAllStudents(selectAll) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>