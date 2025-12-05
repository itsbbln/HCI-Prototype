<?php
require_once '../../includes/header.php';
checkRole(['beadle']);
$user = $_SESSION['user'];

$db = new JsonDB();
$success = '';
$error = '';

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Verify current password
    if ($currentPassword !== $user['password']) {
        $error = 'Current password is incorrect.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'New passwords do not match.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        if ($db->update('users', $user['id'], ['password' => $newPassword])) {
            $_SESSION['user']['password'] = $newPassword;
            $success = 'Password changed successfully!';
        } else {
            $error = 'Failed to change password. Please try again.';
        }
    }
}

// Handle account deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $confirmPassword = $_POST['delete_password'];
    
    if ($confirmPassword !== $user['password']) {
        $error = 'Password is incorrect. Account deletion cancelled.';
    } else {
        // In a real system, you'd want to soft-delete or archive
        $error = 'Account deletion is not available in demo mode. Please contact administration.';
    }
}
?>

<div class="top-bar">
    <h1>Welcome, <span class="welcome-name"><?php echo $user['name']; ?></span>!</h1>
    <div class="user-info">
        <div class="notification-icon">
            🔔
            <span class="notification-badge">2</span>
        </div>
        <div class="user-profile-dropdown">
            <div class="profile-dropdown-toggle">
                <div class="user-avatar"><?php echo substr($user['name'], 0, 2); ?></div>
                <div>
                    <div style="font-size: 14px; font-weight: 600;"><?php echo $user['name']; ?></div>
                    <div style="font-size: 12px; color: #6e6e6e;">
                        <?php echo ucfirst($user['role']); ?>
                    </div>
                </div>
                <span class="dropdown-arrow">▼</span>
            </div>
            <div class="profile-dropdown-menu">
                <div class="profile-dropdown-header">
                    <div class="user-name"><?php echo $user['name']; ?></div>
                    <div class="user-email"><?php echo $user['email']; ?></div>
                </div>
                <a href="profile.php" class="profile-dropdown-item">
                    <span class="icon">👤</span>
                    <span>My Profile</span>
                </a>
                <a href="settings.php" class="profile-dropdown-item">
                    <span class="icon">⚙️</span>
                    <span>Settings</span>
                </a>
                <div class="profile-dropdown-divider"></div>
                <a href="<?php echo PathHelper::getIncludesPath(); ?>logout.php" class="profile-dropdown-item">
                    <span class="icon">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php if ($success): ?>
    <div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:8px;margin-bottom:16px;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:16px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="settings-container">
    <!-- Security Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <h3>🔒 Security</h3>
            <p>Manage your password and security preferences</p>
        </div>
        
        <form method="POST" class="settings-form">
            <h4>Change Password</h4>
            
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password" required>
            </div>

            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password" required minlength="6">
                <small style="color:#6b7280;font-size:12px;">Must be at least 6 characters</small>
            </div>

            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm new password" required>
            </div>

            <input type="hidden" name="change_password" value="1">
            <button type="submit" class="btn-submit">Update Password</button>
        </form>
    </div>

    <!-- Notification Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <h3>🔔 Notifications</h3>
            <p>Choose what notifications you want to receive</p>
        </div>
        
        <div class="settings-form">
            <div class="setting-item">
                <div class="setting-info">
                    <h4>Attendance Updates</h4>
                    <p>Get notified when attendance is marked or approved</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="setting-item">
                <div class="setting-info">
                    <h4>Sanction Alerts</h4>
                    <p>Receive notifications about sanctions and violations</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <div class="setting-item">
                <div class="setting-info">
                    <h4>System Updates</h4>
                    <p>Get informed about system changes and maintenance</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <button class="btn-submit" onclick="alert('Notification preferences saved!')">Save Preferences</button>
        </div>
    </div>

    <!-- Account Settings -->
    <div class="settings-card">
        <div class="settings-header">
            <h3>👤 Account</h3>
            <p>Manage your account settings and preferences</p>
        </div>
        
        <div class="settings-form">
            <div class="info-row">
                <div class="info-label">Account Type:</div>
                <div class="info-value"><?php echo ucfirst($user['role']); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Member Since:</div>
                <div class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">User ID:</div>
                <div class="info-value">#<?php echo str_pad($user['id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>

            <hr style="margin: 24px 0; border: none; border-top: 1px solid #e5e7eb;">

            <a href="profile.php" class="btn-view" style="text-decoration:none;display:inline-block;">
                Edit Profile
            </a>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="settings-card danger-card">
        <div class="settings-header">
            <h3>⚠️ Account Deletion</h3>
            <p>Irreversible actions for your account</p>
        </div>
        
        <form method="POST" class="settings-form" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone!');">
            <div class="danger-warning">
                <strong>Delete Account</strong>
                <p>Once you delete your account, there is no going back. Please be certain.</p>
            </div>

            <div class="form-group">
                <label>Confirm Password to Delete Account</label>
                <input type="password" name="delete_password" placeholder="Enter your password" required>
            </div>

            <input type="hidden" name="delete_account" value="1">
            <button type="submit" class="btn-danger">Delete My Account</button>
        </form>
    </div>
</div>

<style>
    .settings-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 0 20px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .settings-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .settings-header {
        padding: 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .settings-header h3 {
        margin: 0 0 8px 0;
        font-size: 20px;
        color: #111827;
    }

    .settings-header p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .settings-form {
        padding: 24px;
    }

    .settings-form h4 {
        margin: 0 0 16px 0;
        font-size: 16px;
        color: #111827;
    }

    .settings-form .form-group {
        margin-bottom: 20px;
    }

    .settings-form label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
        font-size: 14px;
    }

    .settings-form input {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
    }

    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .setting-item:last-child {
        border-bottom: none;
    }

    .setting-info h4 {
        margin: 0 0 4px 0;
        font-size: 15px;
        color: #111827;
    }

    .setting-info p {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
    }

    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 48px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #cbd5e1;
        transition: 0.4s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    .toggle-switch input:checked + .toggle-slider {
        background-color: #2c55f0;
    }

    .toggle-switch input:checked + .toggle-slider:before {
        transform: translateX(24px);
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e5e7eb;
    }

    .info-row:last-of-type {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: #374151;
        font-size: 14px;
    }

    .info-value {
        color: #6b7280;
        font-size: 14px;
    }

    .danger-card {
        border-color: #fecaca;
    }

    .danger-card .settings-header {
        background: #fef2f2;
        border-bottom-color: #fecaca;
    }

    .danger-warning {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 20px;
    }

    .danger-warning strong {
        display: block;
        color: #991b1b;
        margin-bottom: 4px;
    }

    .danger-warning p {
        margin: 0;
        color: #7f1d1d;
        font-size: 13px;
    }

    .btn-danger {
        background: #ef4444;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
        font-family: 'Poppins', sans-serif;
    }

    .btn-danger:hover {
        background: #dc2626;
    }
</style>

<?php require_once '../../includes/footer.php'; ?>