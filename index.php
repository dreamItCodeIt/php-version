<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

// Get user role and redirect to appropriate dashboard
$userRole = $_SESSION['user_role'];

switch ($userRole) {
    case 'super_admin':
        header("Location: modules/admin/dashboard.php");
        break;
    case 'principal':
        header("Location: modules/principal/dashboard.php");
        break;
    case 'teacher':
        header("Location: modules/teacher/dashboard.php");
        break;
    case 'class_teacher':
        header("Location: modules/class-teacher/dashboard.php");
        break;
    default:
        header("Location: login.php");
}
exit();
?>
