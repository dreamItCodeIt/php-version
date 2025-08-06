<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';
require_once '../classes/Student.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$result = new Result();
$student = new Student();

$academic_year_id = $_GET['academic_year_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;

if (!$academic_year_id || !$term_id) {
    header('Location: index.php');
    exit;
}

// Get term statistics
$term_stats = $result->getTermStatistics($academic_year_id, $term_id);
$division_stats = $result->getDivisionStatistics($academic_year_id, $term_id);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Term Report</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Total Students</h5>
                            <h2 class="text-primary"><?php echo $term_stats['total_students'] ?? 0; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Average Score</h5>
                            <h2 class="text-success"><?php echo number_format($term_stats['average_score'] ?? 0, 2); ?>%</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Pass Rate</h5>
                            <h2 class="text-info"><?php echo number_format($term_stats['pass_rate'] ?? 0, 2); ?>%</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Subjects</h5>
                            <h2 class="text-warning"><?php echo $term_stats['total_subjects'] ?? 0; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Division Analysis</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($division_stats as $division => $count): ?>
                        <div class="col-md-2">
                            <div class="text-center">
                                <h4><?php echo $count; ?></h4>
                                <p>Division <?php echo $division; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Top Performers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Total Points</th>
                                    <th>Average</th>
                                    <th>Division</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $top_performers = $result->getTopPerformers($academic_year_id, $term_id, 10);
                                $position = 1;
                                foreach ($top_performers as $performer): 
                                ?>
                                <tr>
                                    <td><?php echo $position++; ?></td>
                                    <td><?php echo htmlspecialchars($performer['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($performer['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($performer['total_points']); ?></td>
                                    <td><?php echo number_format($performer['average_points'], 2); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($performer['division']); ?></span></td>
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
