<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();

$studentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$student = $db->getById('students', $studentId);
if (!$student) {
    echo '<div class="records-container"><div style="padding:40px;text-align:center;color:#6b7280;">Student not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

$studentSanctions = array_filter($db->getAll('sanctions'), function($s) use ($studentId) {
    return $s['student_id'] == $studentId;
});

?>
            <div class="top-bar page-header">
                <div>
                    <h1>Student Profile</h1>
                    <p>Detailed profile for <?php echo htmlspecialchars($student['name']); ?>.</p>
                </div>
                <a href="student-profiles.php" class="btn-view">Back</a>
            </div>

            <div class="records-container">
                <div style="display:flex;gap:20px;flex-wrap:wrap;">
                    <div style="flex:1;min-width:320px;background:white;padding:20px;border-radius:8px;border:1px solid #e6e6e6;">
                        <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                        <p><strong>Grade & Section:</strong> <?php echo htmlspecialchars($student['grade']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email'] ?? '—'); ?></p>
                        <p><strong>Total Offenses:</strong> <?php echo (int)($student['offenses'] ?? 0); ?></p>
                    </div>
                    <div style="flex:2;min-width:320px;background:white;padding:20px;border-radius:8px;border:1px solid #e6e6e6;">
                        <h3>Sanctions</h3>
                        <?php if (empty($studentSanctions)): ?>
                            <div style="color:#6b7280;padding:12px;border-radius:6px">No sanctions for this student.</div>
                        <?php else: ?>
                            <table class="records-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Violation</th>
                                        <th>Level</th>
                                        <th>Date Issued</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($studentSanctions as $i => $s): ?>
                                    <tr>
                                        <td><?php echo $i+1; ?></td>
                                        <td><?php echo htmlspecialchars($s['violation_type']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($s['sanction_level'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($s['date_issued'])); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($s['status'])); ?></td>
                                        <td><a class="btn-view" href="sanction-details.php?id=<?php echo $s['id']; ?>">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

<?php require_once '../../includes/footer.php'; ?>
