<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Student.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Student Progress Report';
$student_id = $_GET['student_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

if (!$student_id) {
    redirect('index.php');
}

$studentObj = new Student();
$resultObj = new Result();

$student = $studentObj->getById($student_id);
$progressData = [];

if ($academic_year_id) {
    $progressData = $resultObj->getStudentProgress($student_id, $academic_year_id);
}

$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Student Progress Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($progressData)): ?>
                <a href="print.php?type=progress&student_id=<?php echo $student_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
                   class="btn btn-sm btn-primary" target="_blank">
                    <i class="bi bi-printer"></i> Print
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($student): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Registration Number:</strong></td>
                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Class:</strong></td>
                            <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Select Academic Year</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
                        <div class="mb-3">
                            <label for="academic_year_id" class="form-label">Academic Year</label>
                            <select class="form-select" id="academic_year_id" name="academic_year_id" required>
                                <option value="">Select Academic Year</option>
                                <?php foreach ($academicYears as $year): ?>
                                <option value="<?php echo $year['id']; ?>" <?php echo $academic_year_id == $year['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($progressData)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Progress Across Terms</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Term 1</th>
                                    <th>Term 2</th>
                                    <th>Term 3</th>
                                    <th>Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($progressData as $subject => $terms): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject); ?></td>
                                    <td>
                                        <?php if (isset($terms['term1'])): ?>
                                            <?php echo $terms['term1']['marks']; ?> 
                                            <span class="badge bg-<?php echo getGradeBadgeClass($terms['term1']['grade']); ?>">
                                                <?php echo $terms['term1']['grade']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($terms['term2'])): ?>
                                            <?php echo $terms['term2']['marks']; ?> 
                                            <span class="badge bg-<?php echo getGradeBadgeClass($terms['term2']['grade']); ?>">
                                                <?php echo $terms['term2']['grade']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($terms['term3'])): ?>
                                            <?php echo $terms['term3']['marks']; ?> 
                                            <span class="badge bg-<?php echo getGradeBadgeClass($terms['term3']['grade']); ?>">
                                                <?php echo $terms['term3']['grade']; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $progress = calculateProgress($terms);
                                        if ($progress > 0): ?>
                                            <span class="text-success">
                                                <i class="bi bi-arrow-up"></i> +<?php echo $progress; ?>%
                                            </span>
                                        <?php elseif ($progress < 0): ?>
                                            <span class="text-danger">
                                                <i class="bi bi-arrow-down"></i> <?php echo $progress; ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-dash"></i> No change
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i>
        Student not found.
    </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
