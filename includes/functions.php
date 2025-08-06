<?php
require_once 'config.php';

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate random string
 */
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

/**
 * Get academic years
 */
function getAcademicYears() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM academic_years ORDER BY year DESC");
    return $stmt->fetchAll();
}

/**
 * Get current academic year
 */
function getCurrentAcademicYear() {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM academic_years WHERE is_current = 1 LIMIT 1");
    return $stmt->fetch();
}

/**
 * Get subjects by form
 */
function getSubjectsByForm($form) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM subjects WHERE form = ? OR form = 'All' ORDER BY name");
    $stmt->execute([$form]);
    return $stmt->fetchAll();
}

/**
 * Calculate grade from marks
 */
function calculateGrade($marks) {
    if ($marks >= 80) return 'A';
    if ($marks >= 70) return 'B';
    if ($marks >= 60) return 'C';
    if ($marks >= 50) return 'D';
    if ($marks >= 40) return 'E';
    if ($marks >= 30) return 'F';
    return 'F';
}

/**
 * Calculate grade points from marks
 */
function calculateGradePoints($marks) {
    if ($marks >= 80) return 5;
    if ($marks >= 70) return 4;
    if ($marks >= 60) return 3;
    if ($marks >= 50) return 2;
    if ($marks >= 40) return 1;
    return 0;
}

/**
 * Calculate division from average
 */
function calculateDivision($average, $level = 'O-Level') {
    if ($level === 'O-Level') {
        if ($average >= 75) return 'I';
        if ($average >= 60) return 'II';
        if ($average >= 45) return 'III';
        if ($average >= 30) return 'IV';
        return '0';
    } else { // A-Level
        if ($average >= 75) return 'I';
        if ($average >= 65) return 'II';
        if ($average >= 55) return 'III';
        if ($average >= 45) return 'IV';
        return 'F';
    }
}

/**
 * Get students by class
 */
function getStudentsByClass($form, $stream = null) {
    $db = getDB();
    
    if ($stream) {
        $stmt = $db->prepare("SELECT * FROM students WHERE current_form = ? AND stream = ? AND status = 1 ORDER BY name");
        $stmt->execute([$form, $stream]);
    } else {
        $stmt = $db->prepare("SELECT * FROM students WHERE current_form = ? AND status = 1 ORDER BY name");
        $stmt->execute([$form]);
    }
    
    return $stmt->fetchAll();
}

/**
 * Upload file with validation
 */
function uploadFile($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'xlsx', 'xls', 'csv']) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size too large'];
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedTypes)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    $fileName = uniqid() . '.' . $fileExtension;
    $filePath = UPLOAD_PATH . $fileName;
    
    if (!file_exists(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return ['success' => true, 'filename' => $fileName, 'path' => $filePath];
    }
    
    return ['success' => false, 'message' => 'Failed to save file'];
}

/**
 * Log activity
 */
function logActivity($userId, $action, $description = '') {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO activity_logs (user_id, action, description, created_at) 
        VALUES (?, ?, ?, NOW())
    ");
    return $stmt->execute([$userId, $action, $description]);
}

/**
 * Get pagination data
 */
function getPaginationData($page, $perPage, $totalRecords) {
    $totalPages = ceil($totalRecords / $perPage);
    $offset = ($page - 1) * $perPage;
    
    return [
        'current_page' => $page,
        'per_page' => $perPage,
        'total_records' => $totalRecords,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_previous' => $page > 1,
        'has_next' => $page < $totalPages
    ];
}

/**
 * Generate pagination links
 */
function generatePaginationLinks($pagination, $baseUrl) {
    $links = '';
    
    if ($pagination['has_previous']) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] - 1) . '">Previous</a></li>';
    }
    
    for ($i = 1; $i <= $pagination['total_pages']; $i++) {
        $active = ($i == $pagination['current_page']) ? 'active' : '';
        $links .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $baseUrl . '?page=' . $i . '">' . $i . '</a></li>';
    }
    
    if ($pagination['has_next']) {
        $links .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . '?page=' . ($pagination['current_page'] + 1) . '">Next</a></li>';
    }
    
    return $links;
}
?>
