<?php
require_once '../../includes/header.php';
checkRole(['admin']);

$db = new JsonDB();
$classes = $db->getAll('classes');
$teachers = array_filter($db->getAll('users'), fn($u) => $u['role'] === 'teacher');

$success = $error = '';

// === CREATE NEW CLASS ===
if ($_POST && isset($_POST['create_class'])) {
    $data = [
        'name' => trim($_POST['name']),
        'section' => trim($_POST['section']),
        'schedule' => trim($_POST['schedule']),
        'room' => trim($_POST['room']),
        'teacher_id' => $_POST['teacher_id'] ? (int)$_POST['teacher_id'] : null,
        'students' => []
    ];

    if (empty($data['name']) || empty($data['section'])) {
        $error = "Class name and section are required.";
    } else {
        if ($db->insert('classes', $data)) {
            $success = "Class created successfully!";
        } else {
            $error = "Failed to create class.";
        }
    }
}

// === DELETE CLASS ===
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $classId = (int)$_GET['delete'];
    if ($db->delete('classes', $classId)) {
        $success = "Class deleted successfully.";
    } else {
        $error = "Failed to delete class.";
    }
    header("Location: classes.php");
    exit();
}

// === ASSIGN TEACHER (existing) ===
if ($_POST && isset($_POST['assign_teacher'])) {
    $classId = (int)$_POST['class_id'];
    $teacherId = $_POST['teacher_id'] ? (int)$_POST['teacher_id'] : null;

    if ($db->update('classes', $classId, ['teacher_id' => $teacherId])) {
        $success = "Teacher assigned successfully!";
    } else {
        $error = "Failed to assign teacher.";
    }
}
?>

<div class="page-header">
    <h1>Manage Classes</h1>
    <p>Create, edit, and assign teachers to classes</p>
</div>

<?php if ($success): ?>
    <div class="alert success"><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
<?php endif; ?>

<!-- Create New Class Form -->
<div class="records-container" style="margin-bottom: 40px;">
    <h3>Create New Class</h3>
    <form method="POST" style="background:#fff; padding:25px; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,0.05);">
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:15px;">
            <div class="form-group">
                <label>Subject Name</label>
                <input type="text" name="name" placeholder="e.g. English" required>
            </div>
            <div class="form-group">
                <label>Section</label>
                <input type="text" name="section" placeholder="e.g. Grade 10-A" required>
            </div>
            <div class="form-group">
                <label>Schedule</label>
                <input type="text" name="schedule" placeholder="Mon, Wed, Fri • 8:00 AM" required>
            </div>
            <div class="form-group">
                <label>Room</label>
                <input type="text" name="room" placeholder="Room 201" required>
            </div>
            <div class="form-group">
                <label>Assign Teacher (Optional)</label>
                <select name="teacher_id">
                    <option value="">— No Teacher Yet —</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="align-self: end;">
                <button type="submit" name="create_class" class="btn-submit">Create Class</button>
            </div>
        </div>
    </form>
</div>

<!-- Assign Teacher & List -->
<div class="records-container">
    <h3>Assign Teacher to Existing Class</h3>
    <form method="POST" style="background:#fff; padding:20px; border-radius:8px; margin-bottom:30px;">
        <div style="display:grid; grid-template-columns: 1fr 1fr auto; gap:15px; align-items:end;">
            <div>
                <label>Class</label>
                <select name="class_id" required>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['name']) ?> - <?= htmlspecialchars($c['section']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Teacher</label>
                <select name="teacher_id">
                    <option value="">— Remove Teacher —</option>
                    <?php foreach ($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit" name="assign_teacher" class="btn-submit">Update Teacher</button>
            </div>
        </div>
    </form>

    <h3>All Classes</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Subject</th>
                <th>Section</th>
                <th>Schedule</th>
                <th>Room</th>
                <th>Teacher</th>
                <th>Students</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($classes as $c): 
                $teacher = $c['teacher_id'] ? $db->getById('users', $c['teacher_id']) : null;
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                <td><?= htmlspecialchars($c['section']) ?></td>
                <td><?= htmlspecialchars($c['schedule']) ?></td>
                <td><?= htmlspecialchars($c['room']) ?></td>
                <td>
                    <?php if ($teacher): ?>
                        <span class="status-badge status-approved"><?= htmlspecialchars($teacher['name']) ?></span>
                    <?php else: ?>
                        <span class="status-badge status-pending">Not Assigned</span>
                    <?php endif; ?>
                </td>
                <td><?= count($c['students'] ?? []) ?> students</td>
                <td>
                    <a href="?delete=<?= $c['id'] ?>" 
                       onclick="return confirm('Delete this class permanently? This cannot be undone.')"
                       style="color:#ef4444; font-size:14px;">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($classes)): ?>
            <tr><td colspan="7" style="text-align:center; color:#666; padding:40px;">No classes created yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>