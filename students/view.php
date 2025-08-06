<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Student.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'View Student';
$student_id = $_GET['id'] ?? null;

if (!$student_id) {
    redirect('index.php');
}

$studentObj = new Student();
$resultObj = new Result();

$student = $studentObj->getById($student_id);
$recentResults = $resultObj->getStudentRecentResults($student_id, 10);
$statistics = $resultObj->getStudentStatistics($student_id);

if (!$student) {
    redirect('index.php');
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">View Student</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Students
                </a>
                <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit Student
                </a>
                <a href="../results/enter.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-circle"></i> Enter Results
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Full Name:</strong></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Registration Number:</strong></td>
                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Class:</strong></td>
                            <td>
                                <?php if ($student['class_name']): ?>
                                    <a href="../classes/view.php?id=<?php echo $student['class_id']; ?>">
                                        <?php echo htmlspecialchars($student['class_name'] . ' ' . $student['stream']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Not Assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Gender:</strong></td>
                            <td><?php echo ucfirst($student['gender']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date of Birth:</strong></td>
                            <td><?php echo date('F j, Y', strtotime($student['date_of_birth'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Age:</strong></td>
                            <td><?php echo calculateAge($student['date_of_birth']); ?> years</td>
                        </tr>
                        <tr>
                            <td><strong>Guardian Name:</strong></td>
                            <td><?php echo htmlspecialchars($student['guardian_name'] ?? 'Not Provided'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Guardian Phone:</strong></td>
                            <td><?php echo htmlspecialchars($student['guardian_phone'] ?? 'Not Provided'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td><?php echo htmlspecialchars($student['address'] ?? 'Not Provided'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $student['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Enrolled:</strong></td>
                            <td><?php echo date('F j, Y', strtotime($student['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Academic Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($statistics)): ?>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $statistics['total_subjects']; ?></h4>
                                    <p class="mb-0">Subjects Taken</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo number_format($statistics['overall_average'], 1); ?></h4>
                                    <p class="mb-0">Overall Average</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $statistics['best_division']; ?></h4>
                                    <p class="mb-0">Best Division</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $statistics['total_terms']; ?></h4>
                                    <p class="mb-0">Terms Assessed</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Subject Performance</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Average</th>
                                    <th>Best Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistics['subject_performance'] as $subject): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subject['subject_name']); ?></td>
                                    <td><?php echo number_format($subject['average_marks'], 1); ?>%</td>
                                    <td>
                                        <span class="badge bg-<?php echo getGradeBadgeClass($subject['best_grade']); ?>">
                                            <?php echo $subject['best_grade']; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-graph-up fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No academic records yet.</p>
                        <a href="../results/enter.php?student_id=<?php echo $student['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Enter First Results
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($recentResults)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Results</h5>
                    <a href="../reports/student-report.php?student_id=<?php echo $student['id']; ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-text"></i> View All Reports
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Subject</th>
                                    <th>Term</th>
                                    <th>Academic Year</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                    <th>Date Entered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentResults as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['term_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['academic_year']); ?></td>
                                    <td><?php echo $result['marks']; ?>%</td>
                                    <td>
                                        <span class="badge bg-<?php echo getGradeBadgeClass($result['grade']); ?>">
                                            <?php echo $result['grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $result['points']; ?></td>
                                    <td><?php echo date('M j, Y', strtotime($result['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../results/edit.php?id=<?php echo $result['id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../results/view.php?id=<?php echo $result['id']; ?>" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
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
