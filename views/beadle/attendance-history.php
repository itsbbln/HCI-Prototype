<?php
// Beadle role removed. Redirect to unauthorized.
header('Location: ../../unauthorized.php');
exit;
?>
<?php
// Beadle role removed. Redirect to unauthorized.
header('Location: ../../unauthorized.php');
exit;
?>
            <div class="top-bar">
                <h1>Attendance History</h1>
                <div class="user-info">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo substr($user['name'], 0, 2); ?></div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;"><?php echo $user['name']; ?></div>
                            <div style="font-size: 12px; color: #6e6e6e;">Grade 10-A</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="history-filters">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Month</label>
                        <select id="monthFilter">
                            <option value="11">November 2024</option>
                            <option value="10">October 2024</option>
                            <option value="9">September 2024</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Subject</label>
                        <select id="subjectFilter">
                            <option value="">All Subjects</option>
                            <option value="1">English</option>
                            <option value="2">Mathematics</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Status</label>
                        <select id="statusFilter">
                            <option value="">All Status</option>
                            <option value="approved">Approved</option>
                            <option value="pending">Pending</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <button class="btn-view" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>

            <div class="attendance-stats">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $totalSubmissions; ?></div>
                    <div class="stat-label">Total Submissions</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $approvedCount; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $pendingCount; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $averageAttendance; ?>%</div>
                    <div class="stat-label">Average Attendance</div>
                </div>
            </div>

            <div class="recent-activity">
                <h2 class="section-title">Submission History</h2>
                <ul class="activity-list" id="submissionHistory">
                    <?php if (empty($attendanceHistory)): ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4>No submissions found</h4>
                                <p>Try changing your filters</p>
                            </div>
                        </li>
                    <?php else: ?>
                        <?php foreach (array_reverse($attendanceHistory) as $record): 
                            $class = $db->getById('classes', $record['class_id']);
                            $statusClass = $record['status'] === 'approved' ? 'status-approved' : 
                                          ($record['status'] === 'rejected' ? 'status-rejected' : 'status-pending');
                        ?>
                        <li class="activity-item">
                            <div class="activity-info">
                                <h4><?php echo $class['name']; ?> - <?php echo $record['date']; ?></h4>
                                <p><?php echo count($record['present_students']); ?>/<?php echo count($class['students']); ?> students present • 
                                   <span class="status-badge <?php echo $statusClass; ?>">
                                      <?php echo ucfirst($record['status']); ?>
                                   </span>
                                <?php
                                // Beadle role removed. Redirect to unauthorized.
                                header('Location: ../../unauthorized.php');
                                exit;
                                ?>
            </div>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function applyFilters() {
            // This would be implemented with AJAX in a real application
            alert('Filters applied! This would refresh the data in a real application.');
        }
    </script>
<?php require_once '../../includes/footer.php'; ?>