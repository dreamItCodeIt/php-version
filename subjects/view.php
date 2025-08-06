<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Subject.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$pageTitle = 'View Subject';
$subject_id = $_GET['id'] ?? null;

if (!$subject_id) {
    redirect('index.php');
}

$subjectObj = new Subject();
$resultObj = new Result();

$subject = $subjectObj->getById($subject_id);
$statistics = $resultObj->getSubjectStatistics($subject_id);

if (!$subject) {
    redirect('index.php');
}

include '../includes/header.php';
include '../includes/sidebar.php';
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">View Subject</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="index.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Subjects
                </a>
                <a href="edit.php?id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit Subject
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subject Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Subject Name:</strong></td>
                            <td><?php echo htmlspecialchars($subject['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Subject Code:</strong></td>
                            <td><?php echo htmlspecialchars($subject['code']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Type:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $subject['type'] === 'core' ? 'primary' : 'secondary'; ?>">
                                    <?php echo ucfirst($subject['type']); ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Description:</strong></td>
                            <td><?php echo htmlspecialchars($subject['description'] ?? 'No description available'); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-<?php echo $subject['is_active'] ? 'success' : 'danger'; ?>">
                                    <?php echo $subject['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td><?php echo date('F j, Y', strtotime($subject['created_at'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Subject Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($statistics)): ?>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo $statistics['total_students']; ?></h4>
                                    <p class="mb-0">Total Students</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4><?php echo number_format($statistics['average_score'], 1); ?>%</h4>
                                    <p class="mb-0">Average Score</p>
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
                                    <h4><?php echo $statistics['total_classes']; ?></h4>
                                    <p class="mb-0">Classes Taking</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="mt-3">Grade Distribution</h6>
                    <div class="row">
                        <?php foreach ($statistics['grade_distribution'] as $grade => $count): ?>
                        <div class="col-2 text-center">
                            <div class="badge bg-<?php echo getGradeBadgeClass($grade); ?> w-100 p-2">
                                <div><strong><?php echo $grade; ?></strong></div>
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

    <?php if (!empty($statistics['recent_results'])): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Results</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Term</th>
                                    <th>Academic Year</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($statistics['recent_results'] as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['term_name']); ?></td>
                                    <td><?php echo htmlspecialchars($result['academic_year']); ?></td>
                                    <td><?php echo $result['marks']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo getGradeBadgeClass($result['grade']); ?>">
                                            <?php echo $result['grade']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $result['points']; ?></td>
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
