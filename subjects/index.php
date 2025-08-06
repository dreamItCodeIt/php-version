<?php
require_once '../config/config.php';
requireLogin();
requireRole(['super_admin']);

$user = getCurrentUser();
$db = Database::getInstance();
$subjectObj = new Subject();

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
                'code' => sanitize($_POST['code']),
                'level' => sanitize($_POST['level'])
            ];
            
            if ($subjectObj->create($data)) {
                $message = 'Subject created successfully!';
            } else {
                $error = 'Failed to create subject. Please check if subject code already exists.';
            }
        }
    }
}

// Get all subjects
$oLevelSubjects = $subjectObj->getOrdinaryLevel();
$aLevelSubjects = $subjectObj->getAdvancedLevel();

$pageTitle = 'Subject Management';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-book me-2"></i>Subject Management
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="bi bi-plus-circle me-1"></i>Add Subject
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

            <!-- O-Level Subjects -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Ordinary Level Subjects</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($oLevelSubjects)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject Name</th>
                                    <th>Code</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($oLevelSubjects as $subject): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($subject['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($subject['code']); ?></span>
                                    </td>
                                    <td><?php echo ucfirst($subject['level']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $subject['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($subject['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="view.php?id=<?php echo $subject['id']; ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $subject['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
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
                        No O-Level subjects found.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- A-Level Subjects -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Advanced Level Subjects</h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($aLevelSubjects)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Subject Name</th>
                                    <th>Code</th>
                                    <th>Level</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($aLevelSubjects as $subject): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($subject['name']); ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?php echo htmlspecialchars($subject['code']); ?></span>
                                    </td>
                                    <td><?php echo ucfirst($subject['level']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $subject['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($subject['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="view.php?id=<?php echo $subject['id']; ?>" class="btn btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $subject['id']; ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-pencil"></i>
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
                        No A-Level subjects found.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Subject Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="code" class="form-label">Subject Code</label>
                        <input type="text" class="form-control" id="code" name="code" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="level" class="form-label">Level</label>
                        <select class="form-select" id="level" name="level" required>
                            <option value="">Select Level</option>
                            <option value="ordinary">Ordinary Level</option>
                            <option value="advanced">Advanced Level</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
