<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto-redirect to login if not authenticated
function checkAuth() {
    if (!isset($_SESSION['user'])) {
        header("Location: ../login.php");
        exit();
    }
    return $_SESSION['user'];
}

// Check role-based access
function checkRole($allowedRoles) {
    $user = checkAuth();
    if (!in_array($user['role'], $allowedRoles)) {
        header("Location: ../unauthorized.php");
        exit();
    }
    return $user;
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>