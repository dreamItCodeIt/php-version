<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/ClassModel.php';
require_once '../classes/Student.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Manage Class Students';
$class_id = $_GET['class_id'] ?? null;

if (!$class_id) {
    redirect('index.php');
}

$classObj = new ClassModel();
$studentObj = new Student();

$class = $classObj->getById($class_id);
$students = $studentObj->getByClass($class_id);
$availableStudents = $studentObj->getUnassignedStudents();

if (!$class) {
    redirect('index.php');
}

$success = '';
$errors = [];

// Handle student assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'assign') {
        $student_ids = $_POST['student_ids'] ?? [];
        if (!empty($student_ids)) {
            $assigned = 0;
            foreach ($student_ids as $student_id) {
                if ($studentObj->assignToClass($student_id, $class_id)) {
                    $assigned++;
                }
            }
            if ($assigned > 0) {
                $success = "Successfully assigned {$assigned} student(s) to the class.";
                // Refresh data
                $students = $studentObj->getByClass($class_id);
                $availableStudents = $studentObj->getUnassignedStudents();
            }
        }
    } elseif ($action === 'remove') {
        $student_id = $_POST['student_id'] ?? null;
        if ($student_id && $studentObj->removeFromClass($student_id)) {
            $success = "Student removed from class successfully.";
            // Refresh data
            $students = $studentObj->getByClass($class_id);
            $availableStudents = $studentObj->getUnassignedStudents();
        } else {
            $errors[] = "Failed to remove student from class.";
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Students - <?php echo htmlspecialchars($class['name'] . ' ' . $class['stream']); ?></h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Classes
                </a>
                <a href="view.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View Class
                </a>
                <a href="../students/add.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus"></i> Add New Student
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
            <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <!-- Current Students -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Current Students (<?php echo count($students); ?>)</h5>
                    <small class="text-muted">
                        <?php if ($class['capacity']): ?>
                            Capacity: <?php echo count($students); ?>/<?php echo $class['capacity']; ?>
                        <?php endif; ?>
                    </small>
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
                                            <form method="POST" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to remove this student from the class?')">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Remove from Class">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </form>
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
                        <p class="text-muted mt-2">No students assigned to this class yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Available Students -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Assign Students</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableStudents)): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="assign">
                        <div class="mb-3">
                            <label class="form-label">Available Students:</label>
                            <div style="max-height: 300px; overflow-y: auto;">
                                <?php foreach ($availableStudents as $student): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="student_ids[]" value="<?php echo $student['id']; ?>" 
                                           id="student_<?php echo $student['id']; ?>">
                                    <label class="form-check-label" for="student_<?php echo $student['id']; ?>">
                                        <small>
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                            <span class="text-muted"><?php echo htmlspecialchars($student['registration_number']); ?></span>
                                        </small>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus"></i> Assign Selected
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="text-center py-3">
                        <i class="bi bi-person-check fs-3 text-muted"></i>
                        <p class="text-muted mt-2">All students are already assigned to classes.</p>
                        <a href="../students/add.php?class_id=<?php echo $class['id']; ?>" class="btn btn-sm btn-success">
                            <i class="bi bi-plus"></i> Add New Student
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Class Information -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Class:</strong></td>
                            <td><?php echo htmlspecialchars($class['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Stream:</strong></td>
                            <td><?php echo htmlspecialchars($class['stream']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Level:</strong></td>
                            <td><?php echo htmlspecialchars($class['level']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Teacher:</strong></td>
                            <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Not Assigned'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Capacity:</strong></td>
                            <td><?php echo $class['capacity'] ?? 'Not Set'; ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Select all checkbox functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllBtn = document.createElement('button');
    selectAllBtn.type = 'button';
    selectAllBtn.className = 'btn btn-sm btn-outline-secondary mb-2';
    selectAllBtn.innerHTML = '<i class="bi bi-check-all"></i> Select All';
    
    const checkboxContainer = document.querySelector('div[style*="max-height"]');
    if (checkboxContainer) {
        checkboxContainer.parentNode.insertBefore(selectAllBtn, checkboxContainer);
        
        selectAllBtn.addEventListener('click', function() {
            const checkboxes = checkboxContainer.querySelectorAll('input[type="checkbox"]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(cb => cb.checked = !allChecked);
            selectAllBtn.innerHTML = allChecked ? 
                '<i class="bi bi-check-all"></i> Select All' : 
                '<i class="bi bi-square"></i> Deselect All';
        });
    }
});
</script>

<?php include '../includes/footer.php'; ?>
