<?php
require_once '../config/config.php';
requireLogin();
requireRole(['super_admin', 'principal']);

$user = getCurrentUser();
$db = Database::getInstance();
$studentObj = new Student();

// Get current academic year
$currentYear = $db->fetchOne("SELECT * FROM academic_years WHERE is_current = 1");

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $data = [
                'admission_no' => sanitize($_POST['admission_no']),
                'name' => sanitize($_POST['name']),
                'gender' => sanitize($_POST['gender']),
                'date_of_birth' => sanitize($_POST['date_of_birth']),
                'current_form' => (int)$_POST['current_form'],
                'current_term' => (int)($_POST['current_term'] ?? 1),
                'academic_year_id' => $currentYear['id']
            ];
            
            if ($studentObj->create($data)) {
                $message = 'Student created successfully!';
            } else {
                $error = 'Failed to create student. Please check if admission number already exists.';
            }
        }
    }
}

// Get all students
$students = $studentObj->getAll();

// Get classes for assignment
$classes = $db->fetchAll("SELECT * FROM classes WHERE academic_year_id = ? ORDER BY name", [$currentYear['id'] ?? 1]);

$pageTitle = 'Student Management';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-people me-2"></i>Student Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Student
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Students Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">All Students</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($students)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Admission No.</th>
                                    <th>Name</th>
                                    <th>Gender</th>
                                    <th>Date of Birth</th>
                                    <th>Current Form</th>
                                    <th>Status</th>
                                    <th>Academic Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                    </td>
                                    <td><?php echo ucfirst($student['gender']); ?></td>
                                    <td><?php echo formatDate($student['date_of_birth']); ?></td>
                                    <td>Form <?php echo $student['current_form']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $student['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($student['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $student['academic_year']; ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="view.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="subjects.php?id=<?php echo $student['id']; ?>" class="btn btn-outline-info">
                                                <i class="bi bi-book"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No students found. Click "Add Student" to create the first student.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="admission_no" class="form-label">Admission Number</label>
                        <input type="text" class="form-control" id="admission_no" name="admission_no" 
                               value="<?php echo $studentObj->generateAdmissionNumber(); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_form" class="form-label">Current Form</label>
                        <select class="form-select" id="current_form" name="current_form" required>
                            <option value="">Select Form</option>
                            <option value="1">Form 1</option>
                            <option value="2">Form 2</option>
                            <option value="3">Form 3</option>
                            <option value="4">Form 4</option>
                            <option value="5">Form 5</option>
                            <option value="6">Form 6</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Student</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
