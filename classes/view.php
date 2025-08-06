<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/ClassModel.php';
require_once '../classes/Student.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'View Class';
$class_id = $_GET['id'] ?? null;

if (!$class_id) {
    redirect('index.php');
}

$classObj = new ClassModel();
$studentObj = new Student();
$resultObj = new Result();

$class = $classObj->getById($class_id);
$students = $studentObj->getByClass($class_id);
$statistics = $resultObj->getClassStatistics($class_id);

if (!$class) {
    redirect('index.php');
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">View Class</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Classes
                </a>
                <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit Class
                </a>
                <a href="students.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-people"></i> Manage Students
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Class Name:</strong></td>
                            <td><?php echo htmlspecialchars($class['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Stream:</strong></td>
                            <td>
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($class['stream']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Level:</strong></td>
                            <td><?php echo htmlspecialchars($class['level']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Class Teacher:</strong></td>
                            <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Not Assigned'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Capacity:</strong></td>
                            <td><?php echo $class['capacity'] ?? 'Not Set'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Current Students:</strong></td>
                            <td><?php echo count($students); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $class['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $class['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo date('F j, Y', strtotime($class['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($statistics)): ?>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo count($students); ?></h4>
                                    <p class="mb-0">Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo number_format($statistics['class_average'], 1); ?></h4>
                                    <p class="mb-0">Class Average</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo number_format($statistics['pass_rate'], 1); ?>%</h4>
                                    <p class="mb-0">Pass Rate</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $statistics['subjects_offered']; ?></h4>
                                    <p class="mb-0">Subjects</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Division Distribution</h6>
                    <div class="row">
                        <?php foreach ($statistics['division_distribution'] as $division => $count): ?>
                        <div class="col-3 text-center">
                            <div class="badge bg-<?php echo getDivisionBadgeClass($division); ?> w-100 p-2">
                                <div><strong>Div <?php echo $division; ?></strong></div>
                                <div><?php echo $count; ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No statistics available yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Class Students</h5>
                    <a href="../students/add.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Add Student
                    </a>
                </div>
                <div class="card-body">
                    <?php if (!empty($students)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Registration Number</th>
                                    <th>Student Name</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                                    <td>
                                        <a href="../students/view.php?id=<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo ucfirst($student['gender']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($student['date_of_birth'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $student['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $student['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../students/view.php?id=<?php echo $student['id']; ?>" 
                                               class="btn btn-outline-info" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="../students/edit.php?id=<?php echo $student['id']; ?>" 
                                               class="btn btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../results/enter.php?student_id=<?php echo $student['id']; ?>" 
                                               class="btn btn-outline-success" title="Enter Results">
                                                <i class="bi bi-plus-circle"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-people fs-1 text-muted"></i>
                        <p class="text-muted mt-2">No students in this class yet.</p>
                        <a href="../students/add.php?class_id=<?php echo $class['id']; ?>" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Add First Student
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
