<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Student.php';
require_once '../classes/Result.php';
require_once '../classes/Subject.php';
require_once '../classes/DivisionCalculator.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$student = new Student();
$result = new Result();
$subject = new Subject();
$calculator = new DivisionCalculator();

$student_id = $_GET['student_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;

if (!$student_id || !$academic_year_id || !$term_id) {
    header('Location: index.php');
    exit;
}

$student_data = $student->getById($student_id);
$student_results = $result->getStudentResults($student_id, $academic_year_id, $term_id);
$subjects = $subject->getAll();

// Calculate division
$division_data = $calculator->calculateDivision($student_results, $student_data['level']);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Student Report</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Student Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($student_data['first_name'] . ' ' . $student_data['last_name']); ?></p>
                            <p><strong>Admission Number:</strong> <?php echo htmlspecialchars($student_data['admission_number']); ?></p>
                            <p><strong>Form:</strong> <?php echo htmlspecialchars($student_data['form']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Level:</strong> <?php echo htmlspecialchars($student_data['level']); ?></p>
                            <p><strong>Gender:</strong> <?php echo htmlspecialchars($student_data['gender']); ?></p>
                            <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($student_data['date_of_birth']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Academic Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($student_results as $res): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($res['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($res['score']); ?></td>
                                    <td><?php echo htmlspecialchars($res['grade']); ?></td>
                                    <td><?php echo htmlspecialchars($res['points']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h6>Summary</h6>
                                    <p><strong>Total Points:</strong> <?php echo $division_data['total_points']; ?></p>
                                    <p><strong>Average Points:</strong> <?php echo number_format($division_data['average_points'], 2); ?></p>
                                    <p><strong>Division:</strong> <span class="badge bg-primary"><?php echo $division_data['division']; ?></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
