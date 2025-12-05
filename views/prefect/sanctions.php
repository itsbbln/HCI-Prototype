<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();
$prefectData = $auth->getUserData($user['id'], $user['role']);

$success = '';
$error = '';

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_sanction'])) {
        // Add new sanction
        $studentId = $_POST['student_id'];
        $violationType = $_POST['violation_type'];
        $sanctionLevel = $_POST['sanction_level'];
        $dateIssued = $_POST['date_issued'];
        $description = $_POST['description'];
        $assignedSanction = $_POST['assigned_sanction'];
        
        $sanctionData = [
            'student_id' => $studentId,
            'violation_type' => $violationType,
            'sanction_level' => $sanctionLevel,
            'description' => $description,
            'assigned_sanction' => $assignedSanction,
            'date_issued' => $dateIssued,
            'status' => 'active',
            'issued_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        if ($db->insert('sanctions', $sanctionData)) {
            $success = "Sanction added successfully!";
            
            // Update student offense count
            $student = $db->getById('students', $studentId);
            if ($student) {
                $student['offenses'] = ($student['offenses'] ?? 0) + 1;
                $db->update('students', $studentId, ['offenses' => $student['offenses']]);
            }

            // Create notification for the student (if they have a user account)
        if ($student && !empty($student['email'])) {
            $studentUser = $db->getByField('users', 'email', $student['email']);
            if ($studentUser) {
                $db->insert('notifications', [
                    'user_id' => $studentUser['id'],
                    'title' => 'New sanction issued',
                    'message' => sprintf(
                        'You received a %s sanction for "%s" on %s.',
                        $sanctionLevel,
                        $violationType,
                        $dateIssued
                    ),
                    'type' => 'sanction',
                    'is_read' => false
                ]);
            }
            }
        } else {
            $error = "Failed to add sanction. Please try again.";
        }
        } elseif (isset($_POST['resolve_sanction'])) {
        // Resolve sanction
        $sanctionId = (int) $_POST['sanction_id'];

        if ($db->update('sanctions', $sanctionId, ['status' => 'completed'])) {
            $success = "Sanction marked as resolved!";

            // Load sanction to know which student
            $sanction = $db->getById('sanctions', $sanctionId);
            if ($sanction) {
                $student = $db->getById('students', $sanction['student_id']);
                if ($student && !empty($student['email'])) {
                    $studentUser = $db->getByField('users', 'email', $student['email']);
                    if ($studentUser) {
                        $db->insert('notifications', [
                            'user_id' => $studentUser['id'],
                            'title' => 'Sanction updated',
                            'message' => sprintf(
                                'Your sanction for "%s" has been marked as completed.',
                                $sanction['violation_type']
                            ),
                            'type' => 'sanction',
                            'is_read' => false
                        ]);
                    }
                }
            }
        } else {
            $error = "Failed to resolve sanction. Please try again.";
        }
    }
}

// Get sanctions data
$sanctions = $prefectData['sanctions'] ?? [];
$students = $prefectData['students'] ?? [];

// Filter by student if specified
$studentId = $_GET['student_id'] ?? null;
if ($studentId) {
    $sanctions = array_filter($sanctions, function($sanction) use ($studentId) {
        return $sanction['student_id'] == $studentId;
    });
}
?>
            <div class="top-bar page-header">
                <div>
                    <h1>Sanction Management</h1>
                    <p>Track and manage student sanctions and violations.</p>
                </div>
                <button class="btn-new" onclick="openModal('sanctionModal')">+ Add Sanction</button>
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
                    <label>Grade Level</label>
                    <select onchange="filterSanctions()" id="gradeFilter">
                        <option value="">All Grades</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Section</label>
                    <select onchange="filterSanctions()" id="sectionFilter">
                        <option value="">All Sections</option>
                        <option value="A">Grade 10-A</option>
                        <option value="B">Grade 10-B</option>
                        <option value="A">Grade 11-A</option>
                        <option value="B">Grade 11-B</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Sanction Type</label>
                    <select onchange="filterSanctions()" id="typeFilter">
                        <option value="">All Types</option>
                        <option value="minor">Minor</option>
                        <option value="major">Major</option>
                        <option value="severe">Severe</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select onchange="filterSanctions()" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="appealed">Appealed</option>
                    </select>
                </div>
            </div>

            <div class="records-container">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Grade & Section</th>
                            <th>Violation</th>
                            <th>Sanction Type</th>
                            <th>Offense Count</th>
                            <th>Date Issued</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sanctionsTableBody">
                        <?php if (empty($sanctions)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; padding: 40px; color: #6e6e6e;">
                                    No sanctions found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            // Count offenses per student
                            $studentOffenses = [];
                            foreach ($sanctions as $sanction) {
                                $studentId = $sanction['student_id'];
                                if (!isset($studentOffenses[$studentId])) {
                                    $studentOffenses[$studentId] = 0;
                                }
                                $studentOffenses[$studentId]++;
                            }
                            
                            foreach ($sanctions as $index => $sanction): 
                                $student = $db->getById('students', $sanction['student_id']);
                                $offenseCount = $studentOffenses[$sanction['student_id']] ?? 1;
                                $levelClass = 'sanction-' . $sanction['sanction_level'];
                                $statusClass = $sanction['status'] === 'completed' ? 'status-approved' : 
                                              ($sanction['status'] === 'appealed' ? 'status-rejected' : 'status-pending');
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['grade']; ?></td>
                                <td><?php echo $sanction['violation_type']; ?></td>
                                <td>
                                    <span class="sanction-badge <?php echo $levelClass; ?>">
                                        <?php echo ucfirst($sanction['sanction_level']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="offense-count"><?php echo $offenseCount; ?></span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($sanction['date_issued'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($sanction['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewSanctionDetails(<?php echo $sanction['id']; ?>)">View</button>
                                    <?php if ($sanction['status'] == 'active'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="sanction_id" value="<?php echo $sanction['id']; ?>">
                                        <input type="hidden" name="resolve_sanction" value="1">
                                        <button type="submit" class="btn-view" style="background: #10b981; margin-left: 5px;">Resolve</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Add Sanction Modal -->
    <div class="modal" id="sanctionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Sanction</h2>
                <button class="btn-close" onclick="closeModal('sanctionModal')">×</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" required>
                        <option value="">Select student</option>
                        <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo $student['name']; ?> - <?php echo $student['grade']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Violation Type</label>
                    <select name="violation_type" required>
                        <option value="">Select violation type</option>
                        <option value="Late Arrival">Late Arrival</option>
                        <option value="Disruptive Behavior">Disruptive Behavior</option>
                        <option value="Missing Assignment">Missing Assignment</option>
                        <option value="Dress Code Violation">Dress Code Violation</option>
                        <option value="Fighting">Fighting</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Sanction Level</label>
                    <select name="sanction_level" required>
                        <option value="">Select sanction level</option>
                        <option value="minor">Minor</option>
                        <option value="major">Major</option>
                        <option value="severe">Severe</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date of Incident</label>
                    <input type="date" name="date_issued" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe the violation and sanction details..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Assigned Sanction</label>
                    <textarea name="assigned_sanction" placeholder="Describe the specific sanction or consequence..." required></textarea>
                </div>
                <input type="hidden" name="add_sanction" value="1">
                <button type="submit" class="btn-submit">Add Sanction</button>
            </form>
        </div>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function viewSanctionDetails(sanctionId) {
            alert('Viewing sanction details for ID: ' + sanctionId + ' - This feature is under development.');
        }

        function filterSanctions() {
            const gradeFilter = document.getElementById('gradeFilter').value;
            const sectionFilter = document.getElementById('sectionFilter').value;
            const typeFilter = document.getElementById('typeFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            const rows = document.querySelectorAll('#sanctionsTableBody tr');
            
            rows.forEach(row => {
                if (row.cells.length < 9) return; // Skip the "no records" row
                
                const gradeSection = row.cells[2].textContent;
                const sanctionType = row.cells[4].textContent.trim().toLowerCase();
                const status = row.cells[7].textContent.trim().toLowerCase();
                
                const matchesGrade = !gradeFilter || gradeSection.includes(gradeFilter);
                const matchesSection = !sectionFilter || gradeSection.includes(sectionFilter);
                const matchesType = !typeFilter || sanctionType.includes(typeFilter);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                
                row.style.display = matchesGrade && matchesSection && matchesType && matchesStatus ? '' : 'none';
            });
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>