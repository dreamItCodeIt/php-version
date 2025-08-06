<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/ClassModel.php';
require_once '../classes/User.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Edit Class';
$class_id = $_GET['id'] ?? null;

if (!$class_id) {
    redirect('index.php');
}

$classObj = new ClassModel();
$userObj = new User();

$class = $classObj->getById($class_id);
$teachers = $userObj->getByRole('teacher');

if (!$class) {
    redirect('index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $stream = trim($_POST['stream'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $teacher_id = $_POST['teacher_id'] ?? null;
    $capacity = $_POST['capacity'] ?? null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($name)) {
        $errors[] = 'Class name is required.';
    }

    if (empty($stream)) {
        $errors[] = 'Stream is required.';
    }

    if (empty($level)) {
        $errors[] = 'Level is required.';
    }

    if ($capacity && !is_numeric($capacity)) {
        $errors[] = 'Capacity must be a number.';
    }

    // Check if class name and stream combination exists for other classes
    if ($classObj->nameStreamExists($name, $stream, $class_id)) {
        $errors[] = 'Class name and stream combination already exists.';
    }

    if (empty($errors)) {
        $data = [
            'name' => $name,
            'stream' => $stream,
            'level' => $level,
            'teacher_id' => $teacher_id ?: null,
            'capacity' => $capacity ?: null,
            'is_active' => $is_active
        ];

        if ($classObj->update($class_id, $data)) {
            $success = 'Class updated successfully!';
            $class = $classObj->getById($class_id); // Refresh data
        } else {
            $errors[] = 'Failed to update class.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Class</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Classes
                </a>
                <a href="view.php?id=<?php echo $class['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View Class
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
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Class Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($class['name']); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid class name.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stream" class="form-label">Stream <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="stream" name="stream" 
                                           value="<?php echo htmlspecialchars($class['stream']); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid stream.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="level" class="form-label">Level <span class="text-danger">*</span></label>
                                    <select class="form-select" id="level" name="level" required>
                                        <option value="">Select Level</option>
                                        <option value="O-Level" <?php echo $class['level'] === 'O-Level' ? 'selected' : ''; ?>>O-Level</option>
                                        <option value="A-Level" <?php echo $class['level'] === 'A-Level' ? 'selected' : ''; ?>>A-Level</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a level.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="capacity" class="form-label">Capacity</label>
                                    <input type="number" class="form-control" id="capacity" name="capacity" 
                                           value="<?php echo $class['capacity']; ?>" min="1" max="100">
                                    <div class="form-text">Maximum number of students in this class</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="teacher_id" class="form-label">Class Teacher</label>
                                    <select class="form-select" id="teacher_id" name="teacher_id">
                                        <option value="">Select Teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>" 
                                                <?php echo $class['teacher_id'] == $teacher['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($teacher['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               <?php echo $class['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active Class
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Class
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Class Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo date('M j, Y', strtotime($class['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td><?php echo $class['updated_at'] ? date('M j, Y', strtotime($class['updated_at'])) : 'Never'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Current Students:</strong></td>
                            <td><?php echo $class['student_count'] ?? 0; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $class['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $class['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="view.php?id=<?php echo $class['id']; ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <a href="students.php?class_id=<?php echo $class['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-people"></i> Manage Students
                        </a>
                        <a href="../reports/class-performance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-graph-up"></i> View Reports
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="if(confirm('Are you sure you want to delete this class?')) { window.location.href='delete.php?id=<?php echo $class['id']; ?>'; }">
                            <i class="bi bi-trash"></i> Delete Class
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
