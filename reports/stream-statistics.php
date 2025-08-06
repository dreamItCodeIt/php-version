<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Stream Statistics Report';
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

$resultObj = new Result();
$streamData = [];

if ($term_id && $academic_year_id) {
    $streamData = $resultObj->getStreamStatistics($term_id, $academic_year_id);
}

$terms = $resultObj->getTerms();
$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Stream Statistics Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($streamData)): ?>
                <a href="print.php?type=stream_statistics&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
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

    <?php if (!empty($streamData)): ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Stream Performance Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Stream</th>
                                    <th>Total Students</th>
                                    <th>Average Points</th>
                                    <th>Division I</th>
                                    <th>Division II</th>
                                    <th>Division III</th>
                                    <th>Division IV</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($streamData as $stream => $data): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($stream); ?></strong></td>
                                    <td><?php echo $data['total_students']; ?></td>
                                    <td><?php echo number_format($data['average_points'], 2); ?></td>
                                    <td>
                                        <?php echo $data['divisions']['I']; ?>
                                        <small class="text-muted">
                                            (<?php echo number_format(($data['divisions']['I'] / $data['total_students']) * 100, 1); ?>%)
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo $data['divisions']['II']; ?>
                                        <small class="text-muted">
                                            (<?php echo number_format(($data['divisions']['II'] / $data['total_students']) * 100, 1); ?>%)
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo $data['divisions']['III']; ?>
                                        <small class="text-muted">
                                            (<?php echo number_format(($data['divisions']['III'] / $data['total_students']) * 100, 1); ?>%)
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo $data['divisions']['IV']; ?>
                                        <small class="text-muted">
                                            (<?php echo number_format(($data['divisions']['IV'] / $data['total_students']) * 100, 1); ?>%)
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $data['pass_rate'] >= 70 ? 'success' : ($data['pass_rate'] >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($data['pass_rate'], 1); ?>%
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
