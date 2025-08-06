<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Check authentication and role
requireRole('super_admin');

$pageTitle = 'Admin Dashboard';

// Get dashboard statistics
$db = getDB();

// Total counts
$totalStudents = $db->query("SELECT COUNT(*) as count FROM students WHERE status = 1")->fetch()['count'];
$totalTeachers = $db->query("SELECT COUNT(*) as count FROM users WHERE role IN ('teacher', 'class_teacher') AND status = 1")->fetch()['count'];
$totalSubjects = $db->query("SELECT COUNT(*) as count FROM subjects")->fetch()['count'];

// Current academic year data
$currentYear = getCurrentAcademicYear();
if ($currentYear) {
    $currentYearId = $currentYear['id'];
    
    // Students by form for current year
    $studentsByForm = $db->prepare("
        SELECT current_form, COUNT(*) as count 
        FROM students 
        WHERE academic_year_id = ? AND status = 1 
        GROUP BY current_form 
        ORDER BY current_form
    ");
    $studentsByForm->execute([$currentYearId]);
    $formStats = $studentsByForm->fetchAll();
    
    // Recent activities
    $recentActivities = $db->prepare("
        SELECT al.*, u.name as user_name 
        FROM activity_logs al 
        LEFT JOIN users u ON al.user_id = u.id 
        ORDER BY al.created_at DESC 
        LIMIT 10
    ");
    $recentActivities->execute();
    $activities = $recentActivities->fetchAll();
    
    // Results summary for current term
    $resultsCount = $db->prepare("
        SELECT COUNT(*) as count 
        FROM student_results sr 
        JOIN examinations e ON sr.examination_id = e.id 
        WHERE e.academic_year_id = ?
    ");
    $resultsCount->execute([$currentYearId]);
    $totalResults = $resultsCount->fetch()['count'];
}

include '../../includes/header.php';
?>

<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="bi bi-speedometer2 me-2"></i>Admin Dashboard
                </h1>
                <p class="text-muted mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
            </div>
            <div>
                <span class="badge bg-success fs-6">
                    <i class="bi bi-calendar-event me-1"></i>
                    <?php echo $currentYear ? htmlspecialchars($currentYear['year']) : 'No Academic Year Set'; ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Students
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalStudents); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-badge text-primary" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Teachers
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalTeachers); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Subjects
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalSubjects); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-book text-info" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Results Entered
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalResults ?? 0); ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clipboard-data text-warning" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Students by Form Chart -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-bar-chart me-2"></i>Students Distribution by Form
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($formStats)): ?>
                <canvas id="studentsChart" style="height: 300px;"></canvas>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No student data available for visualization</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-lightning me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="students.php" class="btn btn-primary btn-sm">
                        <i class="bi bi-person-plus me-2"></i>Add New Student
                    </a>
                    <a href="users.php" class="btn btn-success btn-sm">
                        <i class="bi bi-people me-2"></i>Manage Users
                    </a>
                    <a href="subjects.php" class="btn btn-info btn-sm">
                        <i class="bi bi-book me-2"></i>Manage Subjects
                    </a>
                    <a href="examinations.php" class="btn btn-warning btn-sm">
                        <i class="bi bi-clipboard-check me-2"></i>Setup Examinations
                    </a>
                    <a href="reports.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>View Reports
                    </a>
                    <a href="import-students.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-upload me-2"></i>Import Data
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="bi bi-clock-history me-2"></i>Recent System Activities
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($activities)): ?>
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-person-circle me-2"></i>
                                    <?php echo htmlspecialchars($activity['user_name'] ?? 'Unknown'); ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($activity['action']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo formatDateTime($activity['created_at']); ?>
                                    </small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No recent activities</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
</style>

<?php
// Add Chart.js script for students distribution
if (!empty($formStats)) {
    $formLabels = json_encode(array_column($formStats, 'current_form'));
    $formData = json_encode(array_column($formStats, 'count'));
    
    $inlineScripts = "
        const ctx = document.getElementById('studentsChart').getContext('2d');
        const studentsChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: $formLabels,
                datasets: [{
                    label: 'Number of Students',
                    data: $formData,
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b',
                        '#858796'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    ";
}

include '../../includes/footer.php';
?>