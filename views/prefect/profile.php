<?php
require_once '../../includes/header.php';
checkRole(['prefect']);
$user = $_SESSION['user'];

$db = new JsonDB();
$success = '';
$error = '';

// Get additional user data based on role
$userData = $auth->getUserData($user['id'], $user['role']);

// If student, get student record
$studentData = null;
if ($user['role'] === 'student') {
    $studentData = $db->getByField('students', 'email', $user['email']);
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // Check if email is already taken by another user
    $existingUser = $db->getByField('users', 'email', $email);
    if ($existingUser && $existingUser['id'] != $user['id']) {
        $error = 'Email is already taken by another user.';
    } else {
        // Update user data
        $updateData = [
            'name' => $name,
            'email' => $email
        ];
        
        if ($db->update('users', $user['id'], $updateData)) {
            // Update session
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            
            // If student, also update student record
            if ($user['role'] === 'student' && $studentData) {
                $db->update('students', $studentData['id'], [
                    'name' => $name,
                    'email' => $email
                ]);
            }
            
            $success = 'Profile updated successfully!';
            $user = $_SESSION['user']; // Refresh user data
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
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

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar-large">
                <?php echo substr($user['name'], 0, 2); ?>
            </div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <p class="profile-role"><?php echo ucfirst($user['role']); ?></p>
                <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <div class="profile-details">
            <h3>Account Information</h3>
            
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="<?php echo ucfirst($user['role']); ?>" disabled>
                    <small style="color:#6b7280;font-size:12px;">Role cannot be changed</small>
                </div>

                <?php if ($user['role'] === 'student' && $studentData): ?>
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" value="<?php echo htmlspecialchars($studentData['student_id']); ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Grade & Section</label>
                    <input type="text" value="<?php echo htmlspecialchars($studentData['grade']); ?>" disabled>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Account Created</label>
                    <input type="text" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                </div>

                <input type="hidden" name="update_profile" value="1">
                <div class="form-actions">
                    <button type="submit" class="btn-submit">Save Changes</button>
                    <a href="settings.php" class="btn-view" style="text-decoration:none;">Go to Settings</a>
                </div>
            </form>
        </div>
    </div>

    <?php if ($user['role'] === 'student' && $studentData): ?>
    <div class="profile-stats-card">
        <h3>My Statistics</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-icon">📊</div>
                <div class="stat-label">Attendance Rate</div>
                <div class="stat-value">
                    <?php 
                    $attendanceRecords = $userData['attendance'] ?? [];
                    $totalClasses = count(array_unique(array_column($attendanceRecords, 'class_id')));
                    $presentCount = count($attendanceRecords);
                    $attendanceRate = $totalClasses > 0 ? round(($presentCount / $totalClasses) * 100) : 0;
                    echo $attendanceRate . '%';
                    ?>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">⚠️</div>
                <div class="stat-label">Total Offenses</div>
                <div class="stat-value"><?php echo $studentData['offenses'] ?? 0; ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📚</div>
                <div class="stat-label">Enrolled Classes</div>
                <div class="stat-value"><?php echo $totalClasses; ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
    }

    .profile-card, .profile-stats-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }

    .profile-header {
        background: linear-gradient(135deg, #2c55f0 0%, #1e40af 100%);
        padding: 40px;
        display: flex;
        align-items: center;
        gap: 24px;
        color: white;
    }

    .profile-avatar-large {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: white;
        color: #2c55f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
    }

    .profile-info h2 {
        margin: 0 0 8px 0;
        font-size: 28px;
    }

    .profile-role {
        display: inline-block;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .profile-email {
        font-size: 14px;
        opacity: 0.9;
    }

    .profile-details {
        padding: 32px 40px;
    }

    .profile-details h3 {
        margin: 0 0 24px 0;
        font-size: 20px;
        color: #111827;
    }

    .profile-form .form-group {
        margin-bottom: 20px;
    }

    .profile-form label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #374151;
        font-size: 14px;
    }

    .profile-form input {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        font-family: 'Poppins', sans-serif;
    }

    .profile-form input:disabled {
        background: #f3f4f6;
        color: #6b7280;
        cursor: not-allowed;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        margin-top: 32px;
    }

    .profile-stats-card {
        padding: 24px;
    }

    .profile-stats-card h3 {
        margin: 0 0 20px 0;
        font-size: 18px;
    }

    .stats-grid {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .stat-item {
        background: #f9fafb;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
    }

    .stat-icon {
        font-size: 32px;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: #111827;
    }

    @media (max-width: 768px) {
        .profile-container {
            grid-template-columns: 1fr;
        }

        .profile-header {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<?php require_once '../../includes/footer.php'; ?>