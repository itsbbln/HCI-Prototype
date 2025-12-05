<?php
require_once '../../includes/header.php';
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$teacherData = $auth->getUserData($user['id'], $user['role']);

// Calculate report stats
$attendanceRecords = array_filter($db->getAll('attendance'), function($record) use ($db, $user) {
    $class = $db->getById('classes', $record['class_id']);
    return $class && $class['teacher_id'] == $user['id'];
});

$totalPresent = 0;
$totalAbsent = 0;
$totalStudents = 0;

foreach ($attendanceRecords as $record) {
    $class = $db->getById('classes', $record['class_id']);
    $totalPresent += count($record['present_students']);
    $totalStudents += count($class['students']);
}

$totalAbsent = $totalStudents - $totalPresent;
$overallAttendance = $totalStudents > 0 ? round(($totalPresent / $totalStudents) * 100) : 0;
?>
            <div class="top-bar page-header">
                <h1>Reports</h1>
                <p>View attendance reports and summaries.</p>
            </div>

            <div class="filter-row">
                <div class="filter-group">
                    <label>Section</label>
                    <select id="sectionSelect">
                        <option>All Sections</option>
                        <option>Grade 10-A</option>
                        <option>Grade 10-B</option>
                        <option>Grade 11-A</option>
                        <option>Grade 11-B</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Subject</label>
                    <select id="subjectSelect">
                        <option>All Subjects</option>
                        <option>English</option>
                        <option>Mathematics</option>
                        <option>Science</option>
                        <option>Filipino</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Month</label>
                    <select id="monthSelect">
                        <option>November 2024</option>
                        <option>October 2024</option>
                        <option>September 2024</option>
                        <option>August 2024</option>
                    </select>
                </div>
            </div>

            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-icon">🟢</div>
                    <div class="summary-value"><?php echo $totalPresent; ?></div>
                    <div class="summary-label">Total Present</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">🔴</div>
                    <div class="summary-value"><?php echo $totalAbsent; ?></div>
                    <div class="summary-label">Total Absent</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">🟡</div>
                    <div class="summary-value">0</div>
                    <div class="summary-label">Total Late</div>
                </div>
                <div class="summary-card">
                    <div class="summary-icon">📊</div>
                    <div class="summary-value"><?php echo $overallAttendance; ?>%</div>
                    <div class="summary-label">Overall Attendance</div>
                </div>
            </div>

            <div class="report-container">
                <div class="report-header">
                    <h2>Attendance Report</h2>
                    <button class="btn-download" onclick="downloadReport()">📥 Download Report</button>
                </div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Section</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>Attendance %</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $students = $db->getAll('students');
                        $teacherStudents = array_filter($students, function($student) use ($teacherData) {
                            foreach ($teacherData['classes'] as $class) {
                                if (in_array($student['id'], $class['students'])) {
                                    return true;
                                }
                            }
                            return false;
                        });
                        
                        foreach ($teacherStudents as $index => $student): 
                            // Calculate student attendance
                            $presentCount = 0;
                            $totalClasses = 0;
                            
                            foreach ($attendanceRecords as $record) {
                                $class = $db->getById('classes', $record['class_id']);
                                if (in_array($student['id'], $class['students'])) {
                                    $totalClasses++;
                                    if (in_array($student['id'], $record['present_students'])) {
                                        $presentCount++;
                                    }
                                }
                            }
                            
                            $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100) : 0;
                            $absentCount = $totalClasses - $presentCount;
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $student['name']; ?></td>
                            <td><?php echo $student['student_id']; ?></td>
                            <td><?php echo $student['grade']; ?></td>
                            <td><?php echo $presentCount; ?></td>
                            <td><?php echo $absentCount; ?></td>
                            <td>0</td>
                            <td><?php echo $attendanceRate; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function downloadReport() {
            alert('Report download functionality would be implemented here.');
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>