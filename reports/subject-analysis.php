<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Subject Analysis Report';
$subject_id = $_GET['subject_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;

$resultObj = new Result();
$analysisData = [];
$subjectInfo = null;

if ($subject_id && $term_id && $academic_year_id) {
    $analysisData = $resultObj->getSubjectAnalysis($subject_id, $term_id, $academic_year_id);
    $subjectInfo = $resultObj->getSubjectInfo($subject_id);
}

$subjects = $resultObj->getSubjects();
$terms = $resultObj->getTerms();
$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Subject Analysis Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($analysisData)): ?>
                <a href="print.php?type=subject_analysis&subject_id=<?php echo $subject_id; ?>&term_id=<?php echo $term_id; ?>&academic_year_id=<?php echo $academic_year_id; ?>" 
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
                                    <label for="subject_id" class="form-label">Subject</label>
                                    <select class="form-select" id="subject_id" name="subject_id" required>
                                        <option value="">Select Subject</option>
                                        <?php foreach ($subjects as $subject): ?>
                                        <option value="<?php echo $subject['id']; ?>" <?php echo $subject_id == $subject['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($subject['name'] . ' (' . $subject['code'] . ')'); ?>
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

    <?php if (!empty($analysisData) && $subjectInfo): ?>
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subject Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Subject:</strong></td>
                            <td><?php echo htmlspecialchars($subjectInfo['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Code:</strong></td>
                            <td><?php echo htmlspecialchars($subjectInfo['code']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td><?php echo htmlspecialchars($subjectInfo['type']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Performance Statistics</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Total Students:</strong></td>
                            <td><?php echo $analysisData['statistics']['total_students']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Average Score:</strong></td>
                            <td><?php echo number_format($analysisData['statistics']['average_score'], 2); ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Pass Rate:</strong></td>
                            <td><?php echo number_format($analysisData['statistics']['pass_rate'], 2); ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Highest Score:</strong></td>
                            <td><?php echo $analysisData['statistics']['highest_score']; ?>%</td>
                        </tr>
                        <tr>
                            <td><strong>Lowest Score:</strong></td>
                            <td><?php echo $analysisData['statistics']['lowest_score']; ?>%</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Grade Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($analysisData['grade_distribution'] as $grade => $count): ?>
                        <div class="col-md-2 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Grade <?php echo $grade; ?></h5>
                                    <p class="card-text">
                                        <strong><?php echo $count; ?></strong> students<br>
                                        <small class="text-muted">
                                            <?php echo number_format(($count / $analysisData['statistics']['total_students']) * 100, 1); ?>%
                                        </small>
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
                    <h5 class="card-title mb-0">Student Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($analysisData['student_results'] as $result): 
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning"><?php echo $rank; ?></span>
                                        <?php else: ?>
                                            <?php echo $rank; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['class_name']); ?></td>
                                    <td><?php echo $result['marks']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getGradeBadgeClass($result['grade']); ?>">
                                            <?php echo $result['grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $result['points']; ?></td>
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
