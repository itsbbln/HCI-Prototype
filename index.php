<?php
session_start();
require_once 'config/path_helper.php';

if (isset($_SESSION['user'])) {
    header("Location: " . PathHelper::getRolePath() . "dashboard.php");
    exit();
} else {
    header("Location: login.php");
    exit();
}
?>