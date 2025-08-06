<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Student.php';
require_once '../classes/Result.php';
require_once '../classes/ClassModel.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$student = new Student();
$result = new Result();
$class = new ClassModel();

$class_id = $_GET['class_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;

if (!$class_id || !$academic_year_id || !$term_id) {
    header('Location: index.php');
    exit;
}

$class_data = $class->getById($class_id);
$class_students = $student->getByClass($class_id);
$class_results = $result->getClassResults($class_id, $academic_year_id, $term_id);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Class Report</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5>Class Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Class:</strong> <?php echo htmlspecialchars($class_data['name']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Level:</strong> <?php echo htmlspecialchars($class_data['level']); ?></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Total Students:</strong> <?php echo count($class_students); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5>Class Performance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Admission Number</th>
                                    <th>Total Points</th>
                                    <th>Average</th>
                                    <th>Division</th>
                                    <th>Position</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $position = 1;
                                foreach ($class_results as $student_result): 
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student_result['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student_result['admission_number']); ?></td>
                                    <td><?php echo htmlspecialchars($student_result['total_points']); ?></td>
                                    <td><?php echo number_format($student_result['average_points'], 2); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($student_result['division']); ?></span></td>
                                    <td><?php echo $position++; ?></td>
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
