<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Grade Distribution Report';
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

$resultObj = new Result();
$distributionData = [];

if ($term_id && $academic_year_id) {
    $distributionData = $resultObj->getGradeDistribution($term_id, $academic_year_id);
}

$terms = $resultObj->getTerms();
$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Grade Distribution Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($distributionData)): ?>
                <a href="print.php?type=grade_distribution&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
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

    <?php if (!empty($distributionData)): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Overall Grade Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($distributionData['overall'] as $grade => $data): ?>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-<?php echo getGradeBadgeClass($grade); ?> text-white">
                                <div class="card-body text-center">
                                    <h4 class="card-title">Grade <?php echo $grade; ?></h4>
                                    <p class="card-text">
                                        <strong><?php echo $data['count']; ?></strong> students<br>
                                        <small><?php echo number_format($data['percentage'], 1); ?>%</small>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Grade Distribution by Subject</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>A</th>
                                    <th>B</th>
                                    <th>C</th>
                                    <th>D</th>
                                    <th>E</th>
                                    <th>F</th>
                                    <th>Total</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($distributionData['by_subject'] as $subject => $grades): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($subject); ?></strong></td>
                                    <?php foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $grade): ?>
                                    <td>
                                        <?php echo $grades[$grade]['count'] ?? 0; ?>
                                        <small class="text-muted">
                                            (<?php echo number_format($grades[$grade]['percentage'] ?? 0, 1); ?>%)
                                        </small>
                                    </td>
                                    <?php endforeach; ?>
                                    <td><strong><?php echo $grades['total']; ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $grades['pass_rate'] >= 70 ? 'success' : ($grades['pass_rate'] >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($grades['pass_rate'], 1); ?>%
                                        </span>
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
</main>

<?php include '../includes/footer.php'; ?>
