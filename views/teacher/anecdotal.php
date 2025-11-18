<?php
require_once '../../includes/header.php';
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$teacherData = $auth->getUserData($user['id'], $user['role']);

$success = '';
$error = '';

if ($_POST) {
    $studentId = $_POST['student_id'];
    $incidentType = $_POST['incident_type'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    
    $anecdotalData = [
        'student_id' => $studentId,
        'incident_type' => $incidentType,
        'date' => $date,
        'description' => $description,
        'reported_by' => $user['id'],
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    if ($db->insert('anecdotal', $anecdotalData)) {
        $success = "Anecdotal report submitted successfully!";
    } else {
        $error = "Failed to submit report. Please try again.";
    }
}

// Get anecdotal records
$anecdotalRecords = $db->getAll('anecdotal');
$teacherRecords = array_filter($anecdotalRecords, function($record) use ($user) {
    return $record['reported_by'] == $user['id'];
});
?>
            <div class="page-header">
                <div>
                    <h1>Anecdotal Records</h1>
                    <p>Submit and manage student behavior reports.</p>
                </div>
                <button class="btn-new" onclick="openModal('reportModal')">+ New Report</button>
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
                    <label>Section</label>
                    <select>
                        <option>All Sections</option>
                        <option>Grade 10-A</option>
                        <option>Grade 10-B</option>
                        <option>Grade 11-A</option>
                        <option>Grade 11-B</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select>
                        <option>All Status</option>
                        <option>Pending</option>
                        <option>Approved</option>
                        <option>Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Date</label>
                    <input type="date" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>

            <div class="records-container">
                <table class="records-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Student Name</th>
                            <th>Section</th>
                            <th>Incident Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teacherRecords)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #6e6e6e;">
                                    No anecdotal reports submitted yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teacherRecords as $index => $record): 
                                $student = $db->getById('students', $record['student_id']);
                                $statusClass = $record['status'] === 'approved' ? 'status-approved' : 
                                              ($record['status'] === 'rejected' ? 'status-rejected' : 'status-pending');
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['grade']; ?></td>
                                <td><?php echo $record['incident_type']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewReport(<?php echo $record['id']; ?>)">View</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal for New Report -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Submit Anecdotal Report</h2>
                <button class="btn-close" onclick="closeModal('reportModal')">×</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Select Student</label>
                    <select name="student_id" required>
                        <option value="">Select student</option>
                        <?php 
                        $allStudents = $db->getAll('students');
                        foreach ($allStudents as $student): 
                        ?>
                        <option value="<?php echo $student['id']; ?>">
                            <?php echo $student['name']; ?> - <?php echo $student['grade']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Incident Type</label>
                    <select name="incident_type" required>
                        <option value="">Select incident type</option>
                        <option value="Late Arrival">Late Arrival</option>
                        <option value="Disruptive Behavior">Disruptive Behavior</option>
                        <option value="Missing Assignment">Missing Assignment</option>
                        <option value="Dress Code Violation">Dress Code Violation</option>
                        <option value="Fighting">Fighting</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date of Incident</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" placeholder="Describe the incident in detail..." required></textarea>
                </div>
                <button type="submit" class="btn-submit">Submit Report</button>
            </form>
        </div>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function viewReport(reportId) {
            alert('Viewing report ID: ' + reportId + ' - This feature is under development.');
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>