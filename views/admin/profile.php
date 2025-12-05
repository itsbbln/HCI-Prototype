<?php
require_once '../../includes/header.php';
checkRole(['admin']);
$user = $_SESSION['user'];
$db = new JsonDB();

if ($_POST && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);

    $existing = $db->getByField('users', 'email', $email);
    if ($existing && $existing['id'] != $user['id']) {
        $error = "Email already in use.";
    } else {
        $db->update('users', $user['id'], ['name' => $name, 'email' => $email]);
        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;
        $success = "Profile updated!";
    }
}
?>

<div class="top-bar page-header">
    <h1>Admin Profile</h1>
</div>

<div class="profile-container">
    <div class="profile-header">
        <div class="user-avatar large"><?php echo substr($user['name'], 0, 2); ?></div>
        <div>
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            <span class="status-badge sanction-severe">Administrator</span>
        </div>
    </div>

    <div class="profile-details">
        <h3>Update Profile</h3>
        <?php if ($success ?? '') echo "<div class='alert success'>$success</div>"; ?>
        <?php if ($error ?? '') echo "<div class='alert error'>$error</div>"; ?>

        <form method="POST" class="profile-form">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-actions">
                <button type="submit" name="update_profile" class="btn-submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>