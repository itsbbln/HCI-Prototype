<?php
require_once '../../includes/header.php';
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$teacherData = $auth->getUserData($user['id'], $user['role']);

$success = '';
$error = '';

// Handle form submission
if ($_POST && isset($_POST['submit_attendance'])) {
    $classId = $_POST['class_id'];
    $date = $_POST['date'];
    $presentStudents = $_POST['present_students'] ?? [];
    
    // Check if attendance already exists for this class and date
    $existingAttendance = array_filter($db->getAll('attendance'), function($record) use ($classId, $date) {
        return $record['class_id'] == $classId && $record['date'] == $date;
    });
    
    if (!empty($existingAttendance)) {
        $error = "Attendance for this class and date already exists!";
    } else {
        $attendanceData = [
            'class_id' => $classId,
            'date' => $date,
            'created_by' => $user['id'],
            'present_students' => $presentStudents,
            'status' => 'approved',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($db->insert('attendance', $attendanceData)) {
            $success = "Attendance marked successfully for " . count($presentStudents) . " students!";
        } else {
            $error = "Failed to mark attendance. Please try again.";
        }
    }
}

// Get URL parameters for class selection
$classId = $_GET['class_id'] ?? null;
$selectedClass = $classId ? $db->getById('classes', $classId) : null;

// Ensure the teacher owns the selected class
if ($selectedClass && $selectedClass['teacher_id'] != $user['id']) {
    $selectedClass = null;
    $error = "You don't have access to this class.";
}

// Get students for the selected class
$classStudents = [];
if ($selectedClass) {
    $allStudents = $db->getAll('students');
    $classStudents = array_filter($allStudents, function($student) use ($selectedClass) {
        return in_array($student['id'], $selectedClass['students']);
    });
}

// Get recent attendance dates for the date dropdown
$attendanceDates = array_unique(array_column($db->getAll('attendance'), 'date'));
?>
            <div class="top-bar page-header">
                <h1>Attendance</h1>
                <p>Track your student attendance.</p>
            </div>

            <?php if ($success): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="filter-row">
                <div class="filter-group">
                    <label>Subject</label>
                    <select id="subjectSelect" onchange="location = 'attendance.php?class_id=' + this.value;">
                        <option value="">Select Subject</option>
                        <?php foreach ($teacherData['classes'] as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $selectedClass && $selectedClass['id'] == $class['id'] ? 'selected' : ''; ?>>
                            <?php echo $class['name']; ?> - <?php echo $class['section']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Section</label>
                    <select id="sectionSelect">
                        <option><?php echo $selectedClass ? $selectedClass['section'] : 'Select class first'; ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date</label>
                    <input type="date" id="dateSelect" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <?php if ($selectedClass): ?>
            <div class="attendance-sheet-container">
                <div class="sheet-header">
                    <h2>Attendance Sheet</h2>
                    <p><?php echo $selectedClass['name']; ?> - <?php echo $selectedClass['section']; ?> - <?php echo $selectedClass['room']; ?></p>
                </div>

                <form method="POST" id="attendanceForm">
                    <input type="hidden" name="class_id" value="<?php echo $selectedClass['id']; ?>">
                    <input type="hidden" name="date" id="formDate" value="<?php echo date('Y-m-d'); ?>">
                    <input type="hidden" name="submit_attendance" value="1">
                    
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th class="checkbox-cell">
                                    <input type="checkbox" id="selectAll" onchange="toggleAllStudents(this)">
                                </th>
                                <th>#</th>
                                <th>Student name</th>
                                <th>Student ID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($classStudents)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: #6e6e6e;">
                                        No students enrolled in this class.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($classStudents as $index => $student): ?>
                                <tr>
                                    <td class="checkbox-cell">
                                        <input type="checkbox" class="student-checkbox" 
                                               name="present_students[]" value="<?php echo $student['id']; ?>" checked>
                                    </td>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo $student['name']; ?></td>
                                    <td><?php echo $student['student_id']; ?></td>
                                    <td>
                                        <span class="status-text present-status">Present</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <?php if (!empty($classStudents)): ?>
                    <div style="margin-top: 20px; text-align: right;">
                        <button type="submit" class="btn-submit">Submit Attendance</button>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            <?php else: ?>
                <div style="background: #f3f4f6; padding: 40px; text-align: center; border-radius: 10px; margin-top: 20px;">
                    <h3>Select a Class</h3>
                    <p>Please select a subject from the dropdown above to mark attendance.</p>
                    <div style="margin-top: 20px;">
                        <h4>Your Classes:</h4>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 15px;">
                            <?php foreach ($teacherData['classes'] as $class): ?>
                            <a href="attendance.php?class_id=<?php echo $class['id']; ?>" 
                               class="btn-view" 
                               style="text-decoration: none;">
                                <?php echo $class['name']; ?> - <?php echo $class['section']; ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Attendance Records -->
            <?php if ($selectedClass): ?>
            <div class="recent-activity" style="margin-top: 30px;">
                <h2 class="section-title">Recent Attendance Records</h2>
                <?php
                $classAttendance = array_filter($db->getAll('attendance'), function($record) use ($selectedClass) {
                    return $record['class_id'] == $selectedClass['id'];
                });
                
                $recentAttendance = array_slice(array_reverse($classAttendance), 0, 5);
                ?>
                
                <?php if (empty($recentAttendance)): ?>
                    <p style="text-align: center; color: #6e6e6e; padding: 20px;">
                        No attendance records found for this class.
                    </p>
                <?php else: ?>
                    <ul class="activity-list">
                        <?php foreach ($recentAttendance as $record): ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4>Attendance - <?php echo date('F j, Y', strtotime($record['date'])); ?></h4>
                                <p><?php echo count($record['present_students']); ?>/<?php echo count($classStudents); ?> students present</p>
                            </div>
                            <span class="activity-time">
                                <span class="status-badge status-approved">Recorded</span>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set today's date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dateSelect').value = today;
            document.getElementById('formDate').value = today;
            
            // Update form date when date select changes
            document.getElementById('dateSelect').addEventListener('change', function() {
                document.getElementById('formDate').value = this.value;
            });
            
            // Setup attendance checkboxes
            setupAttendanceCheckboxes();
        });

        function toggleAllStudents(selectAll) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            const statusTexts = document.querySelectorAll('.present-status');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAll.checked;
            });
            
            statusTexts.forEach(status => {
                status.textContent = selectAll.checked ? 'Present' : 'Absent';
                status.style.color = selectAll.checked ? '#065f46' : '#991b1b';
            });
        }

        function setupAttendanceCheckboxes() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            const statusTexts = document.querySelectorAll('.present-status');
            
            checkboxes.forEach((checkbox, index) => {
                checkbox.addEventListener('change', function() {
                    const status = statusTexts[index];
                    if (this.checked) {
                        status.textContent = 'Present';
                        status.style.color = '#065f46';
                    } else {
                        status.textContent = 'Absent';
                        status.style.color = '#991b1b';
                    }
                    
                    updateSelectAllCheckbox();
                });
            });
        }

        function updateSelectAllCheckbox() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            const selectAll = document.getElementById('selectAll');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            const allUnchecked = Array.from(checkboxes).every(cb => !cb.checked);
            
            if (allChecked) {
                selectAll.checked = true;
                selectAll.indeterminate = false;
            } else if (allUnchecked) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = true;
            }
        }

        // Form submission validation
        document.getElementById('attendanceForm')?.addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            
            if (checkedCount === 0) {
                e.preventDefault();
                alert('Please mark at least one student as present.');
                return false;
            }
            
            const date = document.getElementById('dateSelect').value;
            if (!date) {
                e.preventDefault();
                alert('Please select a date.');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.textContent = 'Submitting...';
            submitBtn.disabled = true;
        });
    </script>

    <style>
        .present-status {
            color: #065f46;
            font-weight: 500;
        }
        
        /* Style for indeterminate checkbox */
        input[type="checkbox"]:indeterminate {
            background: #2c55f0;
            border-color: #2c55f0;
        }
        
        .btn-view {
            display: inline-block;
            padding: 8px 16px;
            background: #2c55f0;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-view:hover {
            background: #173bd1;
            text-decoration: none;
            color: white;
        }
    </style>
<?php require_once '../../includes/footer.php'; ?>