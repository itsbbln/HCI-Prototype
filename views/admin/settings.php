<?php
require_once '../../includes/header.php';
checkRole(['admin']);
$user = $_SESSION['user'];
$db = new JsonDB();
$success = $error = '';

if ($_POST && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($current !== $user['password']) {
        $error = "Current password is incorrect.";
    } elseif ($new !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $db->update('users', $user['id'], ['password' => $new]);
        $_SESSION['user']['password'] = $new;
        $success = "Password changed successfully!";
    }
}
?>

<div class="page-header">
    <h1>Admin Settings</h1>
</div>

<div class="settings-container">
    <div class="settings-card">
        <div class="settings-header">
            <h3>Change Password</h3>
        </div>
        <form method="POST">
            <?php if ($success) echo "<div class='alert success'>$success</div>"; ?>
            <?php if ($error) echo "<div class='alert error'>$error</div>"; ?>

            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" name="change_password" class="btn-submit">Update Password</button>
        </form>
    </div>

    <div class="settings-card danger-card">
        <div class="settings-header">
            <h3>Danger Zone</h3>
        </div>
        <div class="danger-warning">
            <strong>System Administrator Account</strong>
            <p>This is the main admin account. Deletion is disabled for security.</p>
        </div>
        <button class="btn-danger" disabled>Delete Admin Account (Disabled)</button>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>