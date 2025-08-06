<?php
require_once '../config/config.php';
requireLogin();
requireRole(['super_admin']);

$user = getCurrentUser();
$db = Database::getInstance();

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
                'name' => sanitize($_POST['name']),
                'form' => (int)$_POST['form'],
                'level' => sanitize($_POST['level']),
                'academic_year_id' => $currentYear['id'],
                'class_teacher_id' => !empty($_POST['class_teacher_id']) ? (int)$_POST['class_teacher_id'] : null
            ];
            
            $sql = "INSERT INTO classes (name, form, level, academic_year_id, class_teacher_id) VALUES (?, ?, ?, ?, ?)";
            try {
                $db->query($sql, [
                    $data['name'],
                    $data['form'],
                    $data['level'],
                    $data['academic_year_id'],
                    $data['class_teacher_id']
                ]);
                $message = 'Class created successfully!';
            } catch (Exception $e) {
                $error = 'Failed to create class. Please try again.';
            }
        }
    }
}

// Get all classes
$classes = $db->fetchAll("SELECT c.*, u.name as teacher_name, ay.year as academic_year 
                          FROM classes c 
                          LEFT JOIN users u ON c.class_teacher_id = u.id 
                          JOIN academic_years ay ON c.academic_year_id = ay.id 
                          ORDER BY c.form, c.name");

// Get teachers for assignment
$teachers = $db->fetchAll("SELECT * FROM users WHERE role IN ('teacher', 'class_teacher') AND status = 'active' ORDER BY name");

$pageTitle = 'Class Management';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-building me-2"></i>Class Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addClassModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Class
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

            <!-- Classes Table -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">All Classes</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($classes)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Class Name</th>
                                    <th>Form</th>
                                    <th>Level</th>
                                    <th>Class Teacher</th>
                                    <th>Academic Year</th>
                                    <th>Students</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($classes as $class): ?>
                                <?php
                                $studentCount = $db->fetchOne("SELECT COUNT(*) as count FROM student_classes WHERE class_id = ?", [$class['id']])['count'];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($class['name']); ?></strong>
                                    </td>
                                    <td>Form <?php echo $class['form']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $class['level'] === 'ordinary' ? 'primary' : 'success'; ?>">
                                            <?php echo ucfirst($class['level']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($class['teacher_name'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo $class['academic_year']; ?></td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $studentCount; ?> students</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="view.php?id=<?php echo $class['id']; ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $class['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="students.php?id=<?php echo $class['id']; ?>" class="btn btn-outline-info">
                                                <i class="bi bi-people"></i>
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
                        No classes found. Click "Add Class" to create the first class.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Class Modal -->
<div class="modal fade" id="addClassModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Class Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="e.g., Form 1A" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="form" class="form-label">Form</label>
                        <select class="form-select" id="form" name="form" required>
                            <option value="">Select Form</option>
                            <option value="1">Form 1</option>
                            <option value="2">Form 2</option>
                            <option value="3">Form 3</option>
                            <option value="4">Form 4</option>
                            <option value="5">Form 5</option>
                            <option value="6">Form 6</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="">Select Level</option>
                            <option value="ordinary">Ordinary Level</option>
                            <option value="advanced">Advanced Level</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="class_teacher_id" class="form-label">Class Teacher (Optional)</label>
                        <select class="form-select" id="class_teacher_id" name="class_teacher_id">
                            <option value="">Select Class Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo $teacher['id']; ?>">
                                <?php echo htmlspecialchars($teacher['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Class</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-set level based on form selection
document.getElementById('form').addEventListener('change', function() {
    const form = parseInt(this.value);
    const levelSelect = document.getElementById('level');
    
    if (form >= 1 && form <= 4) {
        levelSelect.value = 'ordinary';
    } else if (form >= 5 && form <= 6) {
        levelSelect.value = 'advanced';
    }
});
</script>

<?php include '../includes/footer.php'; ?>
