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

$pageTitle = 'Student Report';
$student_id = $_GET['student_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

if (!$student_id) {
    redirect('index.php');
}

$studentObj = new Student();
$resultObj = new Result();

$student = $studentObj->getById($student_id);
$results = [];
$summary = [];

if ($term_id && $academic_year_id) {
    $results = $resultObj->getStudentResults($student_id, $term_id, $academic_year_id);
    $summary = $resultObj->getStudentSummary($student_id, $term_id, $academic_year_id);
}

// Get available terms and academic years
$terms = $resultObj->getTerms();
$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Student Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($results)): ?>
                <a href="print.php?type=student&student_id=<?php echo $student_id; ?>&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
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
                        <tr>
                            <td><strong>Stream:</strong></td>
                            <td><?php echo htmlspecialchars($student['stream']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Select Report Period</h5>
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
                        <div class="mb-3">
                            <label for="term_id" class="form-label">Term</label>
                            <select class="form-select" id="term_id" name="term_id" required>
                                <option value="">Select Term</option>
                                <?php foreach ($terms as $term): ?>
                                <option value="<?php echo $term['id']; ?>" <?php echo $term_id == $term['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($term['name']); ?>
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

    <?php if (!empty($results)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Academic Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                    <td><?php echo $result['marks']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getGradeBadgeClass($result['grade']); ?>">
                                            <?php echo $result['grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $result['points']; ?></td>
                                    <td><?php echo htmlspecialchars($result['remarks'] ?? ''); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!empty($summary)): ?>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Summary</h6>
                                    <p class="mb-1"><strong>Total Points:</strong> <?php echo $summary['total_points']; ?></p>
                                    <p class="mb-1"><strong>Average Points:</strong> <?php echo number_format($summary['average_points'], 2); ?></p>
                                    <p class="mb-0"><strong>Division:</strong> 
                                        <span class="badge bg-<?php echo getDivisionBadgeClass($summary['division']); ?>">
                                            <?php echo $summary['division']; ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
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
