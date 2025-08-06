<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Class Performance Report';
$class_id = $_GET['class_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

$resultObj = new Result();
$performanceData = [];
$classInfo = null;

if ($class_id && $term_id && $academic_year_id) {
    $performanceData = $resultObj->getClassPerformance($class_id, $term_id, $academic_year_id);
    $classInfo = $resultObj->getClassInfo($class_id);
}

$classes = $resultObj->getClasses();
$terms = $resultObj->getTerms();
$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Class Performance Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($performanceData)): ?>
                <a href="print.php?type=class_performance&class_id=<?php echo $class_id; ?>&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
                   class="btn btn-sm btn-primary" target="_blank">
                    <i class="bi bi-printer"></i> Print
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Select Report Parameters</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="class_id" class="form-label">Class</label>
                                    <select class="form-select" id="class_id" name="class_id" required>
                                        <option value="">Select Class</option>
                                        <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($class['name'] . ' - ' . $class['stream']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            </div>
                            <div class="col-md-4">
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
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($performanceData) && $classInfo): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Class:</strong> <?php echo htmlspecialchars($classInfo['name']); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Stream:</strong> <?php echo htmlspecialchars($classInfo['stream']); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Students:</strong> <?php echo count($performanceData); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Class Teacher:</strong> <?php echo htmlspecialchars($classInfo['teacher_name'] ?? 'Not Assigned'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Student Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Registration Number</th>
                                    <th>Total Points</th>
                                    <th>Average Points</th>
                                    <th>Division</th>
                                    <th>Subjects Taken</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($performanceData as $student): 
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning"><?php echo $rank; ?></span>
                                        <?php else: ?>
                                            <?php echo $rank; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="student-report.php?student_id=<?php echo $student['student_id']; ?>&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>">
                                            <?php echo htmlspecialchars($student['student_name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                    <td><?php echo $student['total_points']; ?></td>
                                    <td><?php echo number_format($student['average_points'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getDivisionBadgeClass($student['division']); ?>">
                                            <?php echo $student['division']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $student['subjects_count']; ?></td>
                                </tr>
                                <?php 
                                $rank++;
                                endforeach; 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
