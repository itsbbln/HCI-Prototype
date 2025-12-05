<?php
require_once '../../includes/header.php';
checkRole(['admin']);
$user = $_SESSION['user'];
$db = new JsonDB();

$stats = [
    'total_students' => count($db->getAll('students')),
    'total_teachers' => count(array_filter($db->getAll('users'), fn($u) => $u['role'] === 'teacher')),
    'total_prefects' => count(array_filter($db->getAll('users'), fn($u) => $u['role'] === 'prefect')),
    'total_sanctions' => count($db->getAll('sanctions')),
    'pending_sanctions' => count(array_filter($db->getAll('sanctions'), fn($s) => $s['status'] === 'pending'))
];
?>

<div class="top-bar page-header">
    <h1>Admin Dashboard</h1>
    <p>System Overview & Management</p>
</div>

<div class="summary-cards">
    <div class="info-card">
        <h3>Total Students</h3>
        <p><?php echo $stats['total_students']; ?></p>
    </div>
    <div class="info-card">
        <h3>Teachers</h3>
        <p><?php echo $stats['total_teachers']; ?></p>
    </div>
    <div class="info-card">
        <h3>Prefects</h3>
        <p><?php echo $stats['total_prefects']; ?></p>
    </div>
    <div class="info-card">
        <h3>Total Sanctions</h3>
        <p><?php echo $stats['total_sanctions']; ?></p>
    </div>
    <div class="info-card" style="border-left: 4px solid #f59e0b;">
        <h3>Pending Review</h3>
        <p><?php echo $stats['pending_sanctions']; ?></p>
    </div>
</div>

<div class="quick-actions">
    <a href="prefects.php" class="action-card">
        <span>👤</span>
        <div>
            <h3>Manage Prefects</h3>
            <p>Create & assign prefect accounts</p>
        </div>
    </a>
    <a href="users.php" class="action-card">
        <span>📋</span>
        <div>
            <h3>All Users</h3>
            <p>View and manage all system users</p>
        </div>
    </a>
</div>

<?php require_once '../../includes/footer.php'; ?>