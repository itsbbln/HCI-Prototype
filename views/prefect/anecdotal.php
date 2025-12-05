<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();
$prefectData = $auth->getUserData($user['id'], $user['role']);

$anecdotalRecords = $db->getAll('anecdotal');

if ($_POST && isset($_POST['action'])) {
    $reportId = $_POST['report_id'];
    $action = $_POST['action'];
    
    if ($db->update('anecdotal', $reportId, ['status' => $action])) {
        $success = "Report " . $action . " successfully!";
    } else {
        $error = "Failed to update report. Please try again.";
    }
    
    // Refresh data
    $anecdotalRecords = $db->getAll('anecdotal');
}
?>
            <div class="top-bar page-header">
                <div>
                    <h1>Anecdotal Records</h1>
                    <p>Review and manage teacher-submitted behavior reports.</p>
                </div>
            </div>

            <?php if (isset($success)): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="filter-row">
                <div class="filter-group">
                    <label>Status</label>
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Incident Type</label>
                    <select id="incidentFilter">
                        <option value="">All Types</option>
                        <option value="Late Arrival">Late Arrival</option>
                        <option value="Disruptive Behavior">Disruptive Behavior</option>
                        <option value="Missing Assignment">Missing Assignment</option>
                        <option value="Dress Code Violation">Dress Code Violation</option>
                    </select>
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
                            <th>Reported By</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($anecdotalRecords)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 40px; color: #6e6e6e;">
                                    No anecdotal reports found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($anecdotalRecords as $index => $record): 
                                $student = $db->getById('students', $record['student_id']);
                                $reporter = $db->getById('users', $record['reported_by']);
                                $statusClass = $record['status'] === 'approved' ? 'status-approved' : 
                                              ($record['status'] === 'rejected' ? 'status-rejected' : 'status-pending');
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                <td><?php echo $student['name']; ?></td>
                                <td><?php echo $student['grade']; ?></td>
                                <td><?php echo $record['incident_type']; ?></td>
                                <td><?php echo $reporter['name']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($record['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($record['status'] == 'pending'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $record['id']; ?>">
                                        <input type="hidden" name="action" value="approved">
                                        <button type="submit" class="btn-view" style="background: #10b981;">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="report_id" value="<?php echo $record['id']; ?>">
                                        <input type="hidden" name="action" value="rejected">
                                        <button type="submit" class="btn-view" style="background: #ef4444;">Reject</button>
                                    </form>
                                    <?php endif; ?>
                                    <button class="btn-view" onclick="viewReportDetails(<?php echo $record['id']; ?>)">Details</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function viewReportDetails(reportId) {
            alert('Viewing detailed report for ID: ' + reportId + ' - This feature is under development.');
        }

        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', filterReports);
        document.getElementById('incidentFilter').addEventListener('change', filterReports);

        function filterReports() {
            const statusFilter = document.getElementById('statusFilter').value;
            const incidentFilter = document.getElementById('incidentFilter').value;
            const rows = document.querySelectorAll('.records-table tbody tr');
            
            rows.forEach(row => {
                if (row.cells.length < 8) return; // Skip the "no records" row
                
                const status = row.cells[6].textContent.trim().toLowerCase();
                const incident = row.cells[4].textContent.trim();
                
                const matchesStatus = !statusFilter || status === statusFilter;
                const matchesIncident = !incidentFilter || incident === incidentFilter;
                
                row.style.display = matchesStatus && matchesIncident ? '' : 'none';
            });
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>