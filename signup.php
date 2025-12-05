<?php
session_start();
require_once 'includes/auth.php';
require_once 'config/path_helper.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: " . PathHelper::getRolePath() . "dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_POST) {
    $auth = new Auth();
    
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    if ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $result = $auth->register($name, $email, $password, $role);
        if ($result === true) {
            $success = "Account created successfully! Please login.";
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Attendance System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo PathHelper::getCssPath(); ?>styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="left">
            <h1>Discipline <br><span>starts with presence.</span></h1>
            <p>Track attendance, monitor growth, and build integrity.</p>
        </div>

        <div class="signup-card">
            <h2>Sign Up</h2>
            
            <?php if ($error): ?>
                <div style="color: #ef4444; background: #fef2f2; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div style="color: #065f46; background: #d1fae5; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your full name" value="<?php echo $_POST['name'] ?? ''; ?>" required>
                
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
                
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" placeholder="Confirm password" required>
                
                <label>Role</label>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="teacher" <?php echo ($_POST['role'] ?? '') == 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                    <option value="student" <?php echo ($_POST['role'] ?? '') == 'student' ? 'selected' : ''; ?>>Student</option>
                </select>
                <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">
                    Note: Prefect accounts must be created by administrator
                </small>
                
                <button type="submit">Sign Up</button>
                
                <div class="login">
                    Already have an account? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>