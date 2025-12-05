<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();
$prefectData = $auth->getUserData($user['id'], $user['role']);

$students = $prefectData['students'] ?? [];
$sanctions = $prefectData['sanctions'] ?? [];
$attendance = $db->getAll('attendance');

// Calculate student statistics
$studentStats = [];
foreach ($students as $student) {
    $studentAttendance = array_filter($attendance, function($record) use ($student) {
        return in_array($student['id'], $record['present_students']);
    });
    
    $attendanceRate = count($attendance) > 0 ? round((count($studentAttendance) / count($attendance)) * 100) : 0;
    
    $activeSanctions = array_filter($sanctions, function($sanction) use ($student) {
        return $sanction['student_id'] == $student['id'] && $sanction['status'] == 'active';
    });
    
    $totalOffenses = count(array_filter($sanctions, function($sanction) use ($student) {
        return $sanction['student_id'] == $student['id'];
    }));
    
    $studentStats[$student['id']] = [
        'attendance_rate' => $attendanceRate,
        'active_sanctions' => count($activeSanctions),
        'total_offenses' => $totalOffenses
    ];
}
?>
            <div class="top-bar page-header">
                <div>
                    <h1>Student Profiles</h1>
                    <p>View and manage student information and records.</p>
                </div>
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Search Student</label>
                        <input type="text" id="searchStudent" placeholder="Search by name or ID...">
                    </div>
                    <div class="filter-group">
                        <label>Grade Level</label>
                        <select id="gradeFilter">
                            <option value="">All Grades</option>
                            <option value="10">Grade 10</option>
                            <option value="11">Grade 11</option>
                            <option value="12">Grade 12</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="records-container">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Grade & Section</th>
                            <th>Attendance Rate</th>
                            <th>Active Sanctions</th>
                            <th>Total Offenses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <?php foreach ($students as $index => $student): 
                            $stats = $studentStats[$student['id']] ?? ['attendance_rate' => 0, 'active_sanctions' => 0, 'total_offenses' => 0];
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $student['student_id']; ?></td>
                            <td><?php echo $student['name']; ?></td>
                            <td><?php echo $student['grade']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span><?php echo $stats['attendance_rate']; ?>%</span>
                                    <div style="width: 60px; height: 6px; background: #e5e7eb; border-radius: 3px;">
                                        <div style="width: <?php echo $stats['attendance_rate']; ?>%; height: 100%; background: <?php echo $stats['attendance_rate'] >= 80 ? '#10b981' : ($stats['attendance_rate'] >= 60 ? '#f59e0b' : '#ef4444'); ?>; border-radius: 3px;"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($stats['active_sanctions'] > 0): ?>
                                    <span class="sanction-badge sanction-<?php echo $stats['active_sanctions'] > 2 ? 'severe' : ($stats['active_sanctions'] > 1 ? 'major' : 'minor'); ?>">
                                        <?php echo $stats['active_sanctions']; ?> active
                                    </span>
                                <?php else: ?>
                                    <span style="color: #6b7280;">None</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="offense-count"><?php echo $stats['total_offenses']; ?></span>
                            </td>
                            <td>
                                <button class="btn-view" onclick="viewStudentProfile(<?php echo $student['id']; ?>)">View</button>
                                <a href="sanctions.php?student_id=<?php echo $student['id']; ?>" class="btn-view" style="background: #f59e0b;">Sanctions</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function viewStudentProfile(studentId) {
            // Navigate to the prefect view-student page
            window.location.href = 'view-student.php?student_id=' + encodeURIComponent(studentId);
        }

        // Search functionality
        document.getElementById('searchStudent').addEventListener('input', filterStudents);
        document.getElementById('gradeFilter').addEventListener('change', filterStudents);

        function filterStudents() {
            const searchTerm = document.getElementById('searchStudent').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value;
            const rows = document.querySelectorAll('#studentsTableBody tr');
            
            rows.forEach(row => {
                const name = row.cells[2].textContent.toLowerCase();
                const studentId = row.cells[1].textContent.toLowerCase();
                const grade = row.cells[3].textContent;
                
                const matchesSearch = name.includes(searchTerm) || studentId.includes(searchTerm);
                const matchesGrade = !gradeFilter || grade.includes(gradeFilter);
                
                row.style.display = matchesSearch && matchesGrade ? '' : 'none';
            });
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>