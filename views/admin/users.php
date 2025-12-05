<?php
require_once '../../includes/header.php';
checkRole(['admin']);

$db = new JsonDB();
$users = $db->getAll('users');
$students = $db->getAll('students');
$success = $error = '';

// Optional: Add manual user creation (beyond prefects)
if ($_POST && isset($_POST['create_user'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?: '123456';
    $role = $_POST['role'];

    if ($db->getByField('users', 'email', $email)) {
        $error = "Email already exists.";
    } else {
        $db->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);
        $success = "User created successfully!";
        header("Location: users.php");
        exit;
    }
}
?>

<div class="top-bar page-header">
    <h1>All Users</h1>
    <p>Manage teachers, prefects, and students</p>
</div>

<?php if ($success): ?><div class="alert success"><?php echo $success; ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?php echo $error; ?></div><?php endif; ?>

<div class="records-container">
    <button onclick="openModal('createUserModal')" class="btn-submit" style="margin-bottom: 20px;">
        + Create New User
    </button>

    <table class="report-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><?php echo htmlspecialchars($u['name']); ?></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <span class="status-badge <?php 
                        echo $u['role'] === 'admin' ? 'sanction-severe' : 
                            ($u['role'] === 'prefect' ? 'sanction-major' : 
                            ($u['role'] === 'teacher' ? 'status-approved' : 'status-pending'));
                    ?>">
                        <?php echo ucfirst($u['role']); ?>
                    </span>
                </td>
                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
</div>

<!-- Create User Modal -->
<div id="createUserModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('createUserModal')">&times;</span>
        <h3>Create New User</h3>
        <form method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Role</label>
                <select name="role" required>
                    <option value="teacher">Teacher</option>
                    <option value="prefect">Prefect</option>
                    <!-- Beadle role removed -->
                    <option value="student">Student</option>
                </select>
            </div>
            <button type="submit" name="create_user" class="btn-submit">Create User</button>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>