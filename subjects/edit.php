<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Subject.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'Edit Subject';
$subject_id = $_GET['id'] ?? null;

if (!$subject_id) {
    redirect('index.php');
}

$subjectObj = new Subject();
$subject = $subjectObj->getById($subject_id);

if (!$subject) {
    redirect('index.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $type = $_POST['type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (empty($name)) {
        $errors[] = 'Subject name is required.';
    }

    if (empty($code)) {
        $errors[] = 'Subject code is required.';
    }

    if (empty($type)) {
        $errors[] = 'Subject type is required.';
    }

    // Check if code exists for other subjects
    if ($subjectObj->codeExists($code, $subject_id)) {
        $errors[] = 'Subject code already exists.';
    }

    if (empty($errors)) {
        $data = [
            'name' => $name,
            'code' => $code,
            'type' => $type,
            'description' => $description,
            'is_active' => $is_active
        ];

        if ($subjectObj->update($subject_id, $data)) {
            $success = 'Subject updated successfully!';
            $subject = $subjectObj->getById($subject_id); // Refresh data
        } else {
            $errors[] = 'Failed to update subject.';
        }
    }
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Subject</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Subjects
                </a>
                <a href="view.php?id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-info">
                    <i class="bi bi-eye"></i> View Subject
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
                    <h5 class="card-title mb-0">Subject Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Subject Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($subject['name']); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid subject name.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Subject Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="code" name="code" 
                                           value="<?php echo htmlspecialchars($subject['code']); ?>" required>
                                    <div class="invalid-feedback">
                                        Please provide a valid subject code.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Subject Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="core" <?php echo $subject['type'] === 'core' ? 'selected' : ''; ?>>Core</option>
                                        <option value="elective" <?php echo $subject['type'] === 'elective' ? 'selected' : ''; ?>>Elective</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a subject type.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               <?php echo $subject['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active Subject
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($subject['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg"></i> Update Subject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subject Details</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo date('M j, Y', strtotime($subject['created_at'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Last Updated:</strong></td>
                            <td><?php echo $subject['updated_at'] ? date('M j, Y', strtotime($subject['updated_at'])) : 'Never'; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $subject['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $subject['is_active'] ? 'Active' : 'Inactive'; ?>
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
                        <a href="view.php?id=<?php echo $subject['id']; ?>" class="btn btn-outline-info btn-sm">
                            <i class="bi bi-eye"></i> View Details
                        </a>
                        <a href="../reports/subject-analysis.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-graph-up"></i> View Reports
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                onclick="if(confirm('Are you sure you want to delete this subject?')) { window.location.href='delete.php?id=<?php echo $subject['id']; ?>'; }">
                            <i class="bi bi-trash"></i> Delete Subject
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
