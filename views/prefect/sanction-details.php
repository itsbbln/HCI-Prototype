<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sanction = $db->getById('sanctions', $id);
if (!$sanction) {
    echo '<div class="records-container"><div style="padding:40px;text-align:center;color:#6b7280;">Sanction not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

$student = $db->getById('students', $sanction['student_id']);

?>
            <div class="top-bar page-header">
                <div>
                    <h1>Sanction Details</h1>
                    <p>Details for sanction ID #<?php echo $sanction['id']; ?>.</p>
                </div>
                <div>
                    <a href="sanctions.php" class="btn-view">Back</a>
                </div>
            </div>

            <div class="records-container">
                <div style="display:flex;gap:20px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:320px;background:white;padding:20px;border-radius:8px;border:1px solid #e6e6e6;">
                        <h3><?php echo htmlspecialchars($student['name'] ?? 'Unknown Student'); ?></h3>
                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id'] ?? '—'); ?></p>
                        <p><strong>Grade & Section:</strong> <?php echo htmlspecialchars($student['grade'] ?? '—'); ?></p>
                        <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($sanction['status'])); ?></p>
                    </div>
                    <div style="flex:2;min-width:320px;background:white;padding:20px;border-radius:8px;border:1px solid #e6e6e6;">
                        <h3><?php echo htmlspecialchars($sanction['violation_type']); ?></h3>
                        <p><strong>Date Issued:</strong> <?php echo date('M j, Y', strtotime($sanction['date_issued'])); ?></p>
                        <p><strong>Sanction Level:</strong> <?php echo htmlspecialchars(ucfirst($sanction['sanction_level'])); ?></p>
                        <h4>Description</h4>
                        <p style="white-space:pre-wrap;"><?php echo htmlspecialchars($sanction['description'] ?? '—'); ?></p>
                        <h4>Assigned Sanction</h4>
                        <p style="white-space:pre-wrap;"><?php echo htmlspecialchars($sanction['assigned_sanction'] ?? '—'); ?></p>
                        <div style="margin-top:12px;display:flex;gap:8px;">
                            <?php if ($sanction['status'] !== 'completed'): ?>
                                <form method="POST" action="sanctions.php" style="display:inline;">
                                    <input type="hidden" name="sanction_id" value="<?php echo $sanction['id']; ?>">
                                    <input type="hidden" name="resolve_sanction" value="1">
                                    <button type="submit" class="btn-view" style="background:#10b981;">Resolve</button>
                                </form>
                            <?php endif; ?>
                            <a href="view-student.php?student_id=<?php echo $sanction['student_id']; ?>" class="btn-view">View Student</a>
                        </div>
                    </div>
                </div>
            </div>

<?php require_once '../../includes/footer.php'; ?>
