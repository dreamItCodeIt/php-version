<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Class Ranking Report';
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

$resultObj = new Result();
$rankingData = [];

if ($term_id && $academic_year_id) {
    $rankingData = $resultObj->getClassRanking($term_id, $academic_year_id);
}

$terms = $resultObj->getTerms();
$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Class Ranking Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($rankingData)): ?>
                <a href="print.php?type=class_ranking&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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

    <?php if (!empty($rankingData)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Rankings</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($rankingData as $className => $classData): ?>
                    <div class="mb-4">
                        <h6 class="text-primary"><?php echo htmlspecialchars($className); ?></h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Position</th>
                                        <th>Student Name</th>
                                        <th>Registration Number</th>
                                        <th>Total Points</th>
                                        <th>Average</th>
                                        <th>Division</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $position = 1;
                                    foreach ($classData['students'] as $student): 
                                    ?>
                                    <tr>
                                        <td>
                                            <?php if ($position <= 3): ?>
                                                <span class="badge bg-warning"><?php echo $position; ?></span>
                                            <?php else: ?>
                                                <?php echo $position; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                        <td><?php echo $student['total_points']; ?></td>
                                        <td><?php echo number_format($student['average_points'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo getDivisionBadgeClass($student['division']); ?>">
                                                <?php echo $student['division']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php 
                                    $position++;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3">
                                <small class="text-muted">Total Students: <?php echo $classData['total_students']; ?></small>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Class Average: <?php echo number_format($classData['class_average'], 2); ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
