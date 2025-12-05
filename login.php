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
if ($_POST) {
    $auth = new Auth();
    
    if ($auth->login($_POST['email'], $_POST['password'])) {
        header("Location: " . PathHelper::getRolePath() . "dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo PathHelper::getCssPath(); ?>styles.css">
    <link rel="stylesheet" href="/HCI-Prototype/css/styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="left">
            <h1>Dologon National High School <br><span>Student Mangement System</span></h1>
            <p>Track attendance, monitor growth, and build integrity.</p>
        </div>

        <div class="login-card">
            <h2>Login</h2>
            
            <?php if ($error): ?>
                <div style="color: #ef4444; background: #fef2f2; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 14px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter email" value="teacher@school.edu" required>
                
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" value="teacher123" required>
                
                <a href="#" class="forgot">Forgot Password?</a>
                
                <button type="submit">Login</button>
                
                <div class="signup">
                    Don't Have an Account? <a href="signup.php">Signup</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>