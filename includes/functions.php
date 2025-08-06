<?php
// Helper Functions

function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login.php');
    }
}

function getCurrentUser() {
    if (isLoggedIn()) {
        $user = new User();
        return $user->findById($_SESSION['user_id']);
    }
    return null;
}

function hasRole($roles) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    if (is_string($roles)) {
        $roles = [$roles];
    }
    
    return in_array($user['role'], $roles);
}

function requireRole($roles) {
    if (!hasRole($roles)) {
        redirect('/dashboard.php?error=unauthorized');
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function calculateGrade($average, $level = 'ordinary') {
    if ($level === 'ordinary') {
        if ($average >= 75) return ['grade' => 'A', 'points' => 1];
        if ($average >= 65) return ['grade' => 'B', 'points' => 2];
        if ($average >= 45) return ['grade' => 'C', 'points' => 3];
        if ($average >= 30) return ['grade' => 'D', 'points' => 4];
        return ['grade' => 'F', 'points' => 5];
    } else {
        if ($average >= 80) return ['grade' => 'A', 'points' => 1];
        if ($average >= 70) return ['grade' => 'B', 'points' => 2];
        if ($average >= 60) return ['grade' => 'C', 'points' => 3];
        if ($average >= 50) return ['grade' => 'D', 'points' => 4];
        if ($average >= 40) return ['grade' => 'E', 'points' => 5];
        return ['grade' => 'F', 'points' => 6];
    }
}

function getGradeColor($grade) {
    switch($grade) {
        case 'A': return 'success';
        case 'B': return 'primary';
        case 'C': return 'warning';
        case 'D': return 'secondary';
        case 'E': return 'danger';
        case 'F': return 'danger';
        default: return 'light';
    }
}

function showAlert($message, $type = 'info') {
    return "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
            </div>";
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
