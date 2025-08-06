<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check authentication and role
requireRole('super_admin');

$pageTitle = 'Manage Students';
$db = getDB();

// Handle form submissions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_student') {
        $name = sanitizeInput($_POST['name']);
        $admission_no = sanitizeInput($_POST['admission_no']);
        $gender = sanitizeInput($_POST['gender']);
        $date_of_birth = sanitizeInput($_POST['date_of_birth']);
        $current_form = sanitizeInput($_POST['current_form']);
        $stream = sanitizeInput($_POST['stream']);
        $current_term = sanitizeInput($_POST['current_term']);
        $academic_year_id = (int) $_POST['academic_year_id'];
        $parent_name = sanitizeInput($_POST['parent_name']);
        $parent_phone = sanitizeInput($_POST['parent_phone']);
        $parent_email = sanitizeInput($_POST['parent_email']);
        $address = sanitizeInput($_POST['address']);
        
        // Validation
        if (empty($name) || empty($admission_no) || empty($gender) || empty($current_form)) {
            $_SESSION['error_message'] = 'Please fill in all required fields.';
        } else {
            // Check if admission number already exists
            $checkStmt = $db->prepare("SELECT id FROM students WHERE admission_no = ?");
            $checkStmt->execute([$admission_no]);
            
            if ($checkStmt->fetch()) {
                $_SESSION['error_message'] = 'Admission number already exists.';
            } else {
                // Insert new student
                $stmt = $db->prepare("
                    INSERT INTO students (admission_no, name, gender, date_of_birth, current_form, stream, 
                                        current_term, academic_year_id, parent_name, parent_phone, parent_email, address) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt->execute([$admission_no, $name, $gender, $date_of_birth, $current_form, $stream, 
                                  $current_term, $academic_year_id, $parent_name, $parent_phone, $parent_email, $address])) {
                    $_SESSION['success_message'] = 'Student added successfully.';
                    logActivity($_SESSION['user_id'], 'add_student', "Added student: $name ($admission_no)");
                } else {
                    $_SESSION['error_message'] = 'Failed to add student. Please try again.';
                }
            }
        }
        
        header("Location: students.php");
        exit();
    }
    
    elseif ($action === 'edit_student') {
        $student_id = (int) $_POST['student_id'];
        $name = sanitizeInput($_POST['name']);
        $admission_no = sanitizeInput($_POST['admission_no']);
        $gender = sanitizeInput($_POST['gender']);
        $date_of_birth = sanitizeInput($_POST['date_of_birth']);
        $current_form = sanitizeInput($_POST['current_form']);
        $stream = sanitizeInput($_POST['stream']);
        $current_term = sanitizeInput($_POST['current_term']);
        $academic_year_id = (int) $_POST['academic_year_id'];
        $parent_name = sanitizeInput($_POST['parent_name']);
        $parent_phone = sanitizeInput($_POST['parent_phone']);
        $parent_email = sanitizeInput($_POST['parent_email']);
        $address = sanitizeInput($_POST['address']);
        $status = (int) $_POST['status'];
        
        // Validation
        if (empty($name) || empty($admission_no) || empty($gender) || empty($current_form)) {
            $_SESSION['error_message'] = 'Please fill in all required fields.';
        } else {
            // Check if admission number already exists for other students
            $checkStmt = $db->prepare("SELECT id FROM students WHERE admission_no = ? AND id != ?");
            $checkStmt->execute([$admission_no, $student_id]);
            
            if ($checkStmt->fetch()) {
                $_SESSION['error_message'] = 'Admission number already exists for another student.';
            } else {
                // Update student
                $stmt = $db->prepare("
                    UPDATE students 
                    SET admission_no = ?, name = ?, gender = ?, date_of_birth = ?, current_form = ?, 
                        stream = ?, current_term = ?, academic_year_id = ?, parent_name = ?, 
                        parent_phone = ?, parent_email = ?, address = ?, status = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$admission_no, $name, $gender, $date_of_birth, $current_form, $stream, 
                                  $current_term, $academic_year_id, $parent_name, $parent_phone, 
                                  $parent_email, $address, $status, $student_id])) {
                    $_SESSION['success_message'] = 'Student updated successfully.';
                    logActivity($_SESSION['user_id'], 'edit_student', "Updated student: $name ($admission_no)");
                } else {
                    $_SESSION['error_message'] = 'Failed to update student. Please try again.';
                }
            }
        }
        
        header("Location: students.php");
        exit();
    }
    
    elseif ($action === 'delete_student') {
        $student_id = (int) $_POST['student_id'];
        
        // Get student info for logging
        $studentStmt = $db->prepare("SELECT name, admission_no FROM students WHERE id = ?");
        $studentStmt->execute([$student_id]);
        $student = $studentStmt->fetch();
        
        if ($student) {
            // Soft delete (set status to 0)
            $stmt = $db->prepare("UPDATE students SET status = 0 WHERE id = ?");
            if ($stmt->execute([$student_id])) {
                $_SESSION['success_message'] = 'Student deleted successfully.';
                logActivity($_SESSION['user_id'], 'delete_student', "Deleted student: {$student['name']} ({$student['admission_no']})");
            } else {
                $_SESSION['error_message'] = 'Failed to delete student.';
            }
        }
        
        header("Location: students.php");
        exit();
    }
}

// Get filter parameters
$form_filter = $_GET['form'] ?? '';
$status_filter = $_GET['status'] ?? '1';

// Build query with filters
$whereConditions = [];
$params = [];

if ($form_filter) {
    $whereConditions[] = "s.current_form = ?";
    $params[] = $form_filter;
}

if ($status_filter !== '') {
    $whereConditions[] = "s.status = ?";
    $params[] = $status_filter;
}

$whereClause = '';
if (!empty($whereConditions)) {
    $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
}

// Get students with pagination
$page = (int) ($_GET['page'] ?? 1);
$perPage = 25;
$offset = ($page - 1) * $perPage;

// Count total records
$countSql = "SELECT COUNT(*) as total FROM students s $whereClause";
$countStmt = $db->prepare($countSql);
$countStmt->execute($params);
$totalRecords = $countStmt->fetch()['total'];

// Get students
$sql = "
    SELECT s.*, ay.year as academic_year 
    FROM students s 
    LEFT JOIN academic_years ay ON s.academic_year_id = ay.id 
    $whereClause 
    ORDER BY s.current_form, s.stream, s.name 
    LIMIT $perPage OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();

// Get academic years for form
$academicYears = getAcademicYears();
$currentAcademicYear = getCurrentAcademicYear();

// Pagination data
$pagination = getPaginationData($page, $perPage, $totalRecords);

include '../../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-person-badge me-2"></i>Manage Students
                </h1>
                <p class="text-muted mb-0">Add, edit, and manage student records</p>
            </div>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                    <i class="bi bi-person-plus me-1"></i>Add New Student
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Filter Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Filter by Form</label>
                        <select name="form" class="form-select">
                            <option value="">All Forms</option>
                            <option value="Form 1" <?php echo $form_filter === 'Form 1' ? 'selected' : ''; ?>>Form 1</option>
                            <option value="Form 2" <?php echo $form_filter === 'Form 2' ? 'selected' : ''; ?>>Form 2</option>
                            <option value="Form 3" <?php echo $form_filter === 'Form 3' ? 'selected' : ''; ?>>Form 3</option>
                            <option value="Form 4" <?php echo $form_filter === 'Form 4' ? 'selected' : ''; ?>>Form 4</option>
                            <option value="Form 5" <?php echo $form_filter === 'Form 5' ? 'selected' : ''; ?>>Form 5</option>
                            <option value="Form 6" <?php echo $form_filter === 'Form 6' ? 'selected' : ''; ?>>Form 6</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                            <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="" <?php echo $status_filter === '' ? 'selected' : ''; ?>>All</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                        <a href="students.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Students Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-table me-2"></i>Students List 
                    <span class="badge bg-primary ms-2"><?php echo number_format($totalRecords); ?> total</span>
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No students found</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                        <i class="bi bi-person-plus me-1"></i>Add First Student
                    </button>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover data-table">
                        <thead>
                            <tr>
                                <th>Admission No</th>
                                <th>Name</th>
                                <th>Form</th>
                                <th>Stream</th>
                                <th>Gender</th>
                                <th>Academic Year</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($student['admission_no']); ?></strong></td>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['current_form']); ?></td>
                                <td><?php echo htmlspecialchars($student['stream']); ?></td>
                                <td>
                                    <i class="bi bi-<?php echo $student['gender'] === 'Male' ? 'person-fill' : 'person-dress'; ?> me-1"></i>
                                    <?php echo htmlspecialchars($student['gender']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($student['academic_year'] ?? 'Not Set'); ?></td>
                                <td>
                                    <?php if ($student['status']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button class="btn btn-outline-primary btn-edit" 
                                                data-student='<?php echo htmlspecialchars(json_encode($student)); ?>'
                                                title="Edit Student">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-delete" 
                                                data-student-id="<?php echo $student['id']; ?>"
                                                data-student-name="<?php echo htmlspecialchars($student['name']); ?>"
                                                title="Delete Student">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Students pagination">
                    <ul class="pagination justify-content-center">
                        <?php echo generatePaginationLinks($pagination, 'students.php'); ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i>Add New Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_student">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Number *</label>
                            <input type="text" class="form-control" name="admission_no" required>
                            <div class="invalid-feedback">Please provide admission number.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" required>
                            <div class="invalid-feedback">Please provide student name.</div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender *</label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <div class="invalid-feedback">Please select gender.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id">
                                <?php foreach ($academicYears as $year): ?>
                                <option value="<?php echo $year['id']; ?>" 
                                        <?php echo $currentAcademicYear && $year['id'] == $currentAcademicYear['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($year['year']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Form *</label>
                            <select class="form-select" name="current_form" required>
                                <option value="">Select Form</option>
                                <option value="Form 1">Form 1</option>
                                <option value="Form 2">Form 2</option>
                                <option value="Form 3">Form 3</option>
                                <option value="Form 4">Form 4</option>
                                <option value="Form 5">Form 5</option>
                                <option value="Form 6">Form 6</option>
                            </select>
                            <div class="invalid-feedback">Please select form.</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stream</label>
                            <input type="text" class="form-control" name="stream" value="A" placeholder="A, B, C, etc.">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Term</label>
                            <select class="form-select" name="current_term">
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="text-muted">Parent/Guardian Information</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent/Guardian Name</label>
                            <input type="text" class="form-control" name="parent_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Phone</label>
                            <input type="tel" class="form-control" name="parent_phone" placeholder="+255...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Email</label>
                            <input type="email" class="form-control" name="parent_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-1"></i>Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Student Modal -->
<div class="modal fade" id="editStudentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil me-2"></i>Edit Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" class="needs-validation" novalidate id="editStudentForm">
                <input type="hidden" name="action" value="edit_student">
                <input type="hidden" name="student_id" id="edit_student_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Admission Number *</label>
                            <input type="text" class="form-control" name="admission_no" id="edit_admission_no" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name *</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Gender *</label>
                            <select class="form-select" name="gender" id="edit_gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth" id="edit_date_of_birth">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Academic Year</label>
                            <select class="form-select" name="academic_year_id" id="edit_academic_year_id">
                                <?php foreach ($academicYears as $year): ?>
                                <option value="<?php echo $year['id']; ?>">
                                    <?php echo htmlspecialchars($year['year']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Form *</label>
                            <select class="form-select" name="current_form" id="edit_current_form" required>
                                <option value="">Select Form</option>
                                <option value="Form 1">Form 1</option>
                                <option value="Form 2">Form 2</option>
                                <option value="Form 3">Form 3</option>
                                <option value="Form 4">Form 4</option>
                                <option value="Form 5">Form 5</option>
                                <option value="Form 6">Form 6</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stream</label>
                            <input type="text" class="form-control" name="stream" id="edit_stream">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Current Term</label>
                            <select class="form-select" name="current_term" id="edit_current_term">
                                <option value="Term 1">Term 1</option>
                                <option value="Term 2">Term 2</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="text-muted">Parent/Guardian Information</h6>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent/Guardian Name</label>
                            <input type="text" class="form-control" name="parent_name" id="edit_parent_name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Phone</label>
                            <input type="tel" class="form-control" name="parent_phone" id="edit_parent_phone">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Email</label>
                            <input type="email" class="form-control" name="parent_email" id="edit_parent_email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" id="edit_address" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check me-1"></i>Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Handle edit button click
    $('.btn-edit').on('click', function() {
        const student = $(this).data('student');
        
        // Populate edit form
        $('#edit_student_id').val(student.id);
        $('#edit_admission_no').val(student.admission_no);
        $('#edit_name').val(student.name);
        $('#edit_gender').val(student.gender);
        $('#edit_date_of_birth').val(student.date_of_birth);
        $('#edit_current_form').val(student.current_form);
        $('#edit_stream').val(student.stream);
        $('#edit_current_term').val(student.current_term);
        $('#edit_academic_year_id').val(student.academic_year_id);
        $('#edit_parent_name').val(student.parent_name);
        $('#edit_parent_phone').val(student.parent_phone);
        $('#edit_parent_email').val(student.parent_email);
        $('#edit_address').val(student.address);
        $('#edit_status').val(student.status);
        
        // Show modal
        new bootstrap.Modal($('#editStudentModal')[0]).show();
    });
    
    // Handle delete button click
    $('.btn-delete').on('click', function() {
        const studentId = $(this).data('student-id');
        const studentName = $(this).data('student-name');
        
        SchoolApp.confirm(
            `Are you sure you want to delete student "${studentName}"? This action cannot be undone.`,
            'Delete Student',
            function(confirmed) {
                if (confirmed) {
                    // Create and submit delete form
                    const form = $('<form method="POST" style="display: none;">')
                        .append('<input type="hidden" name="action" value="delete_student">')
                        .append(`<input type="hidden" name="student_id" value="${studentId}">`);
                    
                    $('body').append(form);
                    form.submit();
                }
            }
        );
    });
});
</script>

<?php include '../../includes/footer.php'; ?>