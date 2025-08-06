<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Annual Report';
$academic_year_id = $_GET['academic_year_id'] ?? null;

$resultObj = new Result();
$annualData = [];

if ($academic_year_id) {
    $annualData = $resultObj->getAnnualReport($academic_year_id);
}

$academicYears = $resultObj->getAcademicYears();

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Annual Report</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Reports
                </a>
                <?php if (!empty($annualData)): ?>
                <a href="print.php?type=annual_report&academic_year_id=<?php echo $academic_year_id; ?>" 
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
                    <h5 class="card-title mb-0">Select Academic Year</h5>
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
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($annualData)): ?>
    <!-- Academic Year Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Academic Year Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary"><?php echo $annualData['overview']['total_students']; ?></h4>
                                <p class="text-muted">Total Students</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-success"><?php echo number_format($annualData['overview']['annual_average'], 2); ?></h4>
                                <p class="text-muted">Annual Average</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info"><?php echo number_format($annualData['overview']['pass_rate'], 1); ?>%</h4>
                                <p class="text-muted">Overall Pass Rate</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning"><?php echo $annualData['overview']['total_subjects']; ?></h4>
                                <p class="text-muted">Subjects Offered</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Term Comparison -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Term-by-Term Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Term</th>
                                    <th>Students Assessed</th>
                                    <th>Average Score</th>
                                    <th>Pass Rate</th>
                                    <th>Division I</th>
                                    <th>Division II</th>
                                    <th>Division III</th>
                                    <th>Division IV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($annualData['term_comparison'] as $term): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($term['term_name']); ?></strong></td>
                                    <td><?php echo $term['students_assessed']; ?></td>
                                    <td><?php echo number_format($term['average_score'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $term['pass_rate'] >= 70 ? 'success' : ($term['pass_rate'] >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($term['pass_rate'], 1); ?>%
                                        </span>
                                    </td>
                                    <td><?php echo $term['division_i']; ?></td>
                                    <td><?php echo $term['division_ii']; ?></td>
                                    <td><?php echo $term['division_iii']; ?></td>
                                    <td><?php echo $term['division_iv']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers of the Year -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 10 Performers of the Year</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Annual Average</th>
                                    <th>Best Term</th>
                                    <th>Consistency</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                foreach ($annualData['top_performers'] as $student): 
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning fs-6"><?php echo $rank; ?></span>
                                        <?php else: ?>
                                            <strong><?php echo $rank; ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                                    <td><?php echo number_format($student['annual_average'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($student['best_term']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $student['consistency'] >= 80 ? 'success' : ($student['consistency'] >= 60 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($student['consistency'], 1); ?>%
                                        </span>
                                    </td>
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

    <!-- Subject Performance Analysis -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Annual Subject Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Students</th>
                                    <th>Annual Average</th>
                                    <th>Pass Rate</th>
                                    <th>Improvement</th>
                                    <th>Best Term</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($annualData['subject_analysis'] as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                    <td><?php echo $subject['total_students']; ?></td>
                                    <td><?php echo number_format($subject['annual_average'], 2); ?>%</td>
                                    <td>
                                        <span class="badge bg-<?php echo $subject['pass_rate'] >= 70 ? 'success' : ($subject['pass_rate'] >= 50 ? 'warning' : 'danger'); ?>">
                                            <?php echo number_format($subject['pass_rate'], 1); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($subject['improvement'] > 0): ?>
                                            <span class="text-success">
                                                <i class="bi bi-arrow-up"></i> +<?php echo number_format($subject['improvement'], 1); ?>%
                                            </span>
                                        <?php elseif ($subject['improvement'] < 0): ?>
                                            <span class="text-danger">
                                                <i class="bi bi-arrow-down"></i> <?php echo number_format($subject['improvement'], 1); ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="bi bi-dash"></i> No change
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($subject['best_term']); ?></td>
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
