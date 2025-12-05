<?php
require_once '../../includes/header.php';
checkRole(['admin']);
$db = new JsonDB();
$success = $error = '';

if ($_POST && isset($_POST['create_prefect'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif ($db->getByField('users', 'email', $email)) {
        $error = "Email already exists.";
    } else {
        $result = $db->insert('users', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'prefect'
        ]);
        if ($result) {
            $success = "Prefect account created successfully!";
        } else {
            $error = "Failed to create account.";
        }
    }
}

$prefects = array_filter($db->getAll('users'), fn($u) => $u['role'] === 'prefect');
?>

<div class="top-bar page-header">
    <h1>Manage Prefects</h1>
    <p>Create and view prefect accounts</p>
</div>

<?php if ($success): ?>
    <div class="alert success"><?php echo $success; ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<div class="records-container">
    <div class="modal-content" style="max-width: 500px; margin-bottom: 30px;">
        <h3>Create New Prefect</h3>
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
                <label>Password</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <button type="submit" name="create_prefect" class="btn-submit">Create Prefect Account</button>
        </form>
    </div>

    <h3>Current Prefects</h3>
    <table class="report-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($prefects as $p): ?>
            <tr>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['email']); ?></td>
                <td><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>