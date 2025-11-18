<?php
require_once '../../includes/header.php';
checkRole(['student']);
$user = $_SESSION['user'];

$db = new JsonDB();
$studentData = $auth->getUserData($user['id'], $user['role']);
$student = $studentData['student'] ?? null;
$sanctions = $studentData['sanctions'] ?? [];

// Count offenses by type
$activeSanctions = array_filter($sanctions, function($sanction) {
    return $sanction['status'] == 'active';
});

$completedSanctions = array_filter($sanctions, function($sanction) {
    return $sanction['status'] == 'completed';
});

$minorSanctions = array_filter($sanctions, function($sanction) {
    return $sanction['sanction_level'] == 'minor';
});

$majorSanctions = array_filter($sanctions, function($sanction) {
    return $sanction['sanction_level'] == 'major';
});

$severeSanctions = array_filter($sanctions, function($sanction) {
    return $sanction['sanction_level'] == 'severe';
});
?>
            <div class="top-bar">
                <h1>My Sanctions</h1>
                <div class="user-info">
                    <div class="user-profile">
                        <div class="user-avatar"><?php echo substr($user['name'], 0, 2); ?></div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600;"><?php echo $user['name']; ?></div>
                            <div style="font-size: 12px; color: #6e6e6e;"><?php echo $student['grade'] ?? 'Student'; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="summary-cards">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Active Sanctions</span>
                        <span class="card-icon">⚠️</span>
                    </div>
                    <div class="card-value"><?php echo count($activeSanctions); ?></div>
                    <div class="card-footer">Currently active</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Total Offenses</span>
                        <span class="card-icon">📋</span>
                    </div>
                    <div class="card-value"><?php echo count($sanctions); ?></div>
                    <div class="card-footer">All time</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Minor Violations</span>
                        <span class="card-icon">🟡</span>
                    </div>
                    <div class="card-value"><?php echo count($minorSanctions); ?></div>
                    <div class="card-footer">Less serious</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Major Violations</span>
                        <span class="card-icon">🔴</span>
                    </div>
                    <div class="card-value"><?php echo count($majorSanctions) + count($severeSanctions); ?></div>
                    <div class="card-footer">Serious offenses</div>
                </div>
            </div>

            <div class="records-container">
                <h3 style="margin-bottom: 20px;">Sanction History</h3>
                
                <?php if (empty($sanctions)): ?>
                    <div style="text-align: center; padding: 40px; color: #6e6e6e;">
                        <h4>No sanctions found</h4>
                        <p>You have no sanctions on record. Keep up the good behavior!</p>
                    </div>
                <?php else: ?>
                    <table class="records-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Violation Type</th>
                                <th>Sanction Level</th>
                                <th>Date Issued</th>
                                <th>Assigned Sanction</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sanctions as $index => $sanction): 
                                $levelClass = 'sanction-' . $sanction['sanction_level'];
                                $statusClass = $sanction['status'] === 'completed' ? 'status-approved' : 
                                              ($sanction['status'] === 'appealed' ? 'status-rejected' : 'status-pending');
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $sanction['violation_type']; ?></td>
                                <td>
                                    <span class="sanction-badge <?php echo $levelClass; ?>">
                                        <?php echo ucfirst($sanction['sanction_level']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($sanction['date_issued'])); ?></td>
                                <td><?php echo $sanction['assigned_sanction'] ?? 'Not specified'; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($sanction['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn-view" onclick="viewSanctionDetails(<?php echo $sanction['id']; ?>)">Details</button>
                                    <?php if ($sanction['status'] == 'active'): ?>
                                    <button class="btn-view" style="background: #f59e0b; margin-left: 5px;" onclick="appealSanction(<?php echo $sanction['id']; ?>)">Appeal</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Sanction Details Modal -->
            <div class="modal" id="sanctionDetailsModal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Sanction Details</h2>
                        <button class="btn-close" onclick="closeModal('sanctionDetailsModal')">×</button>
                    </div>
                    <div id="sanctionDetailsContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="../../js/script.js"></script>
    <script>
        function viewSanctionDetails(sanctionId) {
            // In a real application, this would fetch data from the server
            const sanctionData = {
                1: {
                    violation: "Late Arrival",
                    level: "minor",
                    date: "November 15, 2024",
                    description: "Arrived 15 minutes late to English class without valid excuse.",
                    assigned: "Warning and parent notification",
                    status: "active",
                    issuedBy: "Prefect Name"
                }
            };
            
            const sanction = sanctionData[sanctionId] || {
                violation: "Unknown Violation",
                level: "minor",
                date: "Unknown Date",
                description: "No details available.",
                assigned: "Not specified",
                status: "unknown",
                issuedBy: "Unknown"
            };
            
            const levelClass = 'sanction-' + sanction.level;
            const statusClass = sanction.status === 'completed' ? 'status-approved' : 
                              (sanction.status === 'appealed' ? 'status-rejected' : 'status-pending');
            
            document.getElementById('sanctionDetailsContent').innerHTML = `
                <div style="margin-bottom: 20px;">
                    <h3>${sanction.violation}</h3>
                    <p><strong>Date Issued:</strong> ${sanction.date}</p>
                    <p><strong>Sanction Level:</strong> <span class="sanction-badge ${levelClass}">${sanction.level.charAt(0).toUpperCase() + sanction.level.slice(1)}</span></p>
                    <p><strong>Status:</strong> <span class="status-badge ${statusClass}">${sanction.status.charAt(0).toUpperCase() + sanction.status.slice(1)}</span></p>
                    <p><strong>Issued By:</strong> ${sanction.issuedBy}</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <h4>Violation Description</h4>
                    <p>${sanction.description}</p>
                </div>
                <div style="margin-bottom: 20px;">
                    <h4>Assigned Sanction</h4>
                    <p>${sanction.assigned}</p>
                </div>
                ${sanction.status === 'active' ? `
                <div style="background: #fef3c7; padding: 15px; border-radius: 6px;">
                    <h4>Appeal Process</h4>
                    <p>If you believe this sanction was issued in error, you may file an appeal. Please contact the Prefect of Discipline office.</p>
                    <button class="btn-view" style="background: #f59e0b;" onclick="appealSanction(${sanctionId})">File Appeal</button>
                </div>
                ` : ''}
            `;
            
            openModal('sanctionDetailsModal');
        }

        function appealSanction(sanctionId) {
            if (confirm('Are you sure you want to appeal this sanction? You will need to provide justification for the appeal.')) {
                alert('Appeal process initiated for sanction ID: ' + sanctionId + '. In a real application, this would submit an appeal request.');
                // In a real app, this would submit a form or make an API call
            }
        }
    </script>
        <style>
        .sanction-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .sanction-minor {
            background: #fed7aa;
            color: #92400e;
        }
        
        .sanction-major {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .sanction-severe {
            background: #dc2626;
            color: white;
        }
    </style>

<?php require_once '../../includes/footer.php'; ?>