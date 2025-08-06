<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Subject.php';
require_once '../classes/Result.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$subject = new Subject();
$result = new Result();

$subject_id = $_GET['subject_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;

if (!$subject_id || !$academic_year_id || !$term_id) {
    header('Location: index.php');
    exit;
}

$subject_data = $subject->getById($subject_id);
$subject_results = $result->getSubjectResults($subject_id, $academic_year_id, $term_id);

// Calculate statistics
$total_students = count($subject_results);
$total_score = array_sum(array_column($subject_results, 'score'));
$average_score = $total_students > 0 ? $total_score / $total_students : 0;

$grade_distribution = [];
foreach ($subject_results as $res) {
    $grade = $res['grade'];
    $grade_distribution[$grade] = ($grade_distribution[$grade] ?? 0) + 1;
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Subject Report</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Subject Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($subject_data['name']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Code:</strong> <?php echo htmlspecialchars($subject_data['code']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Level:</strong> <?php echo htmlspecialchars($subject_data['level']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6>Performance Statistics</h6>
                        </div>
                        <div class="card-body">
                            <p><strong>Total Students:</strong> <?php echo $total_students; ?></p>
                            <p><strong>Average Score:</strong> <?php echo number_format($average_score, 2); ?>%</p>
                            <p><strong>Highest Score:</strong> <?php echo $total_students > 0 ? max(array_column($subject_results, 'score')) : 0; ?>%</p>
                            <p><strong>Lowest Score:</strong> <?php echo $total_students > 0 ? min(array_column($subject_results, 'score')) : 0; ?>%</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h6>Grade Distribution</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($grade_distribution as $grade => $count): ?>
                            <p><strong>Grade <?php echo $grade; ?>:</strong> <?php echo $count; ?> students</p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Student Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Admission Number</th>
                                    <th>Class</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subject_results as $res): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($res['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($res['admission_number']); ?></td>
                                    <td><?php echo htmlspecialchars($res['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($res['score']); ?>%</td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($res['grade']); ?></span></td>
                                    <td><?php echo htmlspecialchars($res['points']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
