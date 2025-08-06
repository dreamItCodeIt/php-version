<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$db = Database::getInstance();
$userObj = new User();
$subjectObj = new Subject();

// Get current academic year and term
$currentYear = $db->fetchOne("SELECT * FROM academic_years WHERE is_current = 1");
$currentTerm = $db->fetchOne("SELECT * FROM terms WHERE is_current = 1");

// Get subjects based on user role
if (hasRole(['super_admin', 'principal'])) {
    $subjects = $subjectObj->getAll();
} else {
    $subjects = $userObj->getTeacherSubjects($user['id'], $currentYear['id'] ?? 1);
}

$pageTitle = 'Results Management';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-clipboard-data me-2"></i>Results Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="badge bg-primary me-2">
                            <?php echo $currentTerm['name'] ?? 'No Current Term'; ?>
                        </span>
                        <span class="badge bg-secondary">
                            <?php echo $currentYear['year'] ?? 'No Current Year'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php
                    switch ($_GET['error']) {
                        case 'no_subject':
                            echo 'No subject selected.';
                            break;
                        case 'subject_not_found':
                            echo 'Subject not found.';
                            break;
                        case 'unauthorized':
                            echo 'You are not authorized to access this subject.';
                            break;
                        case 'no_current_term':
                            echo 'No current academic term is set. Contact the administrator.';
                            break;
                        default:
                            echo 'An error occurred.';
                    }
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Results saved successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Subjects Grid -->
            <?php if (!empty($subjects)): ?>
            <div class="row">
                <?php foreach ($subjects as $subject): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($subject['name']); ?></h5>
                                    <p class="card-text">
                                        <span class="badge bg-primary"><?php echo $subject['code']; ?></span>
                                        <span class="badge bg-secondary"><?php echo ucfirst($subject['level']); ?></span>
                                    </p>
                                </div>
                                <div class="text-end">
                                    <?php
                                    // Get progress for this subject
                                    $totalStudents = $db->fetchOne("SELECT COUNT(*) as count FROM student_subjects 
                                                                   WHERE subject_id = ? AND academic_year_id = ?", 
                                                                   [$subject['id'], $currentYear['id'] ?? 1])['count'];
                                    
                                    $enteredResults = $db->fetchOne("SELECT COUNT(*) as count FROM results 
                                                                    WHERE subject_id = ? AND term_id = ? 
                                                                    AND ca_marks IS NOT NULL AND exam_marks IS NOT NULL", 
                                                                    [$subject['id'], $currentTerm['id'] ?? 1])['count'];
                                    
                                    $percentage = $totalStudents > 0 ? round(($enteredResults / $totalStudents) * 100, 1) : 0;
                                    ?>
                                    <small class="text-muted"><?php echo $enteredResults; ?>/<?php echo $totalStudents; ?> students</small>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%" 
                                             aria-valuenow="<?php echo $percentage; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo $percentage; ?>% complete</small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <?php if (hasRole(['teacher', 'class_teacher'])): ?>
                                <a href="enter.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-pencil me-1"></i>Enter Results
                                </a>
                                <?php endif; ?>
                                
                                <a href="view.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye me-1"></i>View Results
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                <?php if (hasRole(['teacher', 'class_teacher'])): ?>
                    No subjects assigned to you yet. Contact the administrator to get subjects assigned.
                <?php else: ?>
                    No subjects available in the system.
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <a href="../reports/" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-file-earmark-text me-1"></i>Generate Reports
                            </a>
                        </div>
                        
                        <?php if (hasRole(['super_admin'])): ?>
                        <div class="col-md-3 mb-2">
                            <a href="../subjects/" class="btn btn-outline-success btn-sm w-100">
                                <i class="bi bi-book me-1"></i>Manage Subjects
                            </a>
                        </div>
                        
                        <div class="col-md-3 mb-2">
                            <a href="calculate-divisions.php" class="btn btn-outline-warning btn-sm w-100">
                                <i class="bi bi-calculator me-1"></i>Calculate Divisions
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3 mb-2">
                            <a href="../dashboard.php" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-house me-1"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
