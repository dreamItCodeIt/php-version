<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . APP_NAME : APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Meta tags -->
    <meta name="description" content="School Results Management System for Government Secondary Schools in Tanzania">
    <meta name="author" content="School Administration">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo BASE_URL; ?>/assets/images/favicon.ico">
</head>
<body class="bg-light">
    
    <!-- Navigation Bar -->
    <?php if (isLoggedIn()): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <i class="bi bi-mortarboard-fill me-2"></i>
                <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php
                    $currentUser = getCurrentUser();
                    $userRole = $_SESSION['user_role'];
                    ?>
                    
                    <?php if ($userRole === 'super_admin'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usersDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-people me-1"></i> Users
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/users.php">Manage Users</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/teachers.php">Assign Teachers</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="studentsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-badge me-1"></i> Students
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/students.php">Manage Students</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/import-students.php">Import Students</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="academicDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-calendar-event me-1"></i> Academic
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/subjects.php">Subjects</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/academic-years.php">Academic Years</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/examinations.php">Examinations</a></li>
                        </ul>
                    </li>
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/reports.php">All Reports</a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/modules/admin/analytics.php">Analytics</a></li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($userRole === 'principal'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/principal/reports.php">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i> Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/principal/analytics.php">
                            <i class="bi bi-graph-up me-1"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/principal/students.php">
                            <i class="bi bi-person-badge me-1"></i> Students
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($userRole === 'teacher'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/teacher/subjects.php">
                            <i class="bi bi-book me-1"></i> My Subjects
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/teacher/results.php">
                            <i class="bi bi-clipboard-data me-1"></i> Enter Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/teacher/reports.php">
                            <i class="bi bi-file-earmark-text me-1"></i> Reports
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if ($userRole === 'class_teacher'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/class-teacher/class.php">
                            <i class="bi bi-people me-1"></i> My Class
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/class-teacher/results.php">
                            <i class="bi bi-clipboard-data me-1"></i> Class Results
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/modules/class-teacher/reports.php">
                            <i class="bi bi-file-earmark-text me-1"></i> Class Reports
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- User Menu -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2"></i>
                            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header"><?php echo ucfirst(str_replace('_', ' ', $userRole)); ?></h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/profile.php">
                                <i class="bi bi-person me-2"></i> Profile
                            </a></li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/change-password.php">
                                <i class="bi bi-key me-2"></i> Change Password
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Content Container -->
    <div class="container-fluid">
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['success_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success_message']); endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['error_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error_message']); endif; ?>
        
        <?php if (isset($_SESSION['warning_message'])): ?>
        <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['warning_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['warning_message']); endif; ?>
