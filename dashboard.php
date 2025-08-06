<?php
require_once 'config/config.php';
requireLogin();

$user = getCurrentUser();
$db = Database::getInstance();

// Get current academic year and term
$currentYear = $db->fetchOne("SELECT * FROM academic_years WHERE is_current = 1");
$currentTerm = $db->fetchOne("SELECT * FROM terms WHERE is_current = 1");

// Role-specific dashboard data
$dashboardData = [];

switch ($user['role']) {
    case 'super_admin':
        $dashboardData = [
            'total_students' => $db->fetchOne("SELECT COUNT(*) as count FROM students WHERE status = 'active'")['count'],
            'total_teachers' => $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role IN ('teacher', 'class_teacher') AND status = 'active'")['count'],
            'total_subjects' => $db->fetchOne("SELECT COUNT(*) as count FROM subjects WHERE status = 'active'")['count'],
            'total_classes' => $db->fetchOne("SELECT COUNT(*) as count FROM classes")['count'],
            'recent_results' => $db->fetchAll("SELECT r.*, s.name as student_name, sub.name as subject_name 
                                              FROM results r 
                                              JOIN students s ON r.student_id = s.id 
                                              JOIN subjects sub ON r.subject_id = sub.id 
                                              ORDER BY r.created_at DESC LIMIT 10")
        ];
        break;
        
    case 'principal':
        $divisionStats = [];
        if ($currentTerm && $currentYear) {
            $divisionStats = $db->fetchAll("SELECT division, level, COUNT(*) as count 
                                           FROM student_divisions 
                                           WHERE term_id = ? AND academic_year_id = ? 
                                           GROUP BY division, level", 
                                           [$currentTerm['id'], $currentYear['id']]);
        }
        $dashboardData = [
            'division_stats' => $divisionStats,
            'top_performers' => $db->fetchAll("SELECT sd.*, s.name as student_name 
                                              FROM student_divisions sd 
                                              JOIN students s ON sd.student_id = s.id 
                                              WHERE sd.term_id = ? AND sd.division = 'Division I' 
                                              ORDER BY sd.total_points ASC LIMIT 10", 
                                              [$currentTerm['id'] ?? 0])
        ];
        break;
        
    case 'teacher':
    case 'class_teacher':
        $userObj = new User();
        $assignedSubjects = $userObj->getTeacherSubjects($user['id'], $currentYear['id'] ?? 1);
        
        $resultsProgress = [];
        foreach ($assignedSubjects as $subject) {
            $totalStudents = $db->fetchOne("SELECT COUNT(*) as count FROM student_subjects 
                                           WHERE subject_id = ? AND academic_year_id = ?", 
                                           [$subject['id'], $currentYear['id'] ?? 1])['count'];
            
            $enteredResults = $db->fetchOne("SELECT COUNT(*) as count FROM results 
                                            WHERE subject_id = ? AND term_id = ? AND teacher_id = ? 
                                            AND ca_marks IS NOT NULL AND exam_marks IS NOT NULL", 
                                            [$subject['id'], $currentTerm['id'] ?? 1, $user['id']])['count'];
            
            $resultsProgress[] = [
                'subject' => $subject,
                'total' => $totalStudents,
                'entered' => $enteredResults,
                'percentage' => $totalStudents > 0 ? round(($enteredResults / $totalStudents) * 100, 1) : 0
            ];
        }
        
        $dashboardData = [
            'assigned_subjects' => $assignedSubjects,
            'results_progress' => $resultsProgress
        ];
        
        // Additional data for class teachers
        if ($user['role'] === 'class_teacher') {
            $assignedClass = $db->fetchOne("SELECT * FROM classes WHERE class_teacher_id = ? AND academic_year_id = ?", 
                                          [$user['id'], $currentYear['id'] ?? 1]);
            if ($assignedClass) {
                $classStudents = $db->fetchAll("SELECT s.* FROM students s 
                                               JOIN student_classes sc ON s.id = sc.student_id 
                                               WHERE sc.class_id = ? AND sc.academic_year_id = ?", 
                                               [$assignedClass['id'], $currentYear['id'] ?? 1]);
                
                $dashboardData['assigned_class'] = $assignedClass;
                $dashboardData['class_stats'] = [
                    'total_students' => count($classStudents),
                    'male_students' => count(array_filter($classStudents, fn($s) => $s['gender'] === 'male')),
                    'female_students' => count(array_filter($classStudents, fn($s) => $s['gender'] === 'female'))
                ];
            }
        }
        break;
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="badge bg-primary">
                            <?php echo $currentTerm['name'] ?? 'No Current Term'; ?>
                        </span>
                        <span class="badge bg-secondary">
                            <?php echo $currentYear['year'] ?? 'No Current Year'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="alert alert-info">
                <i class="bi bi-person-circle me-2"></i>
                Welcome back, <strong><?php echo htmlspecialchars($user['name']); ?></strong>! 
                You are logged in as <span class="badge bg-primary"><?php echo ucwords(str_replace('_', ' ', $user['role'])); ?></span>
            </div>

            <?php if ($user['role'] === 'super_admin'): ?>
                <!-- Super Admin Dashboard -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Students</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboardData['total_students']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-people-fill fa-2x text-gray-300"></i>
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
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Teachers</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboardData['total_teachers']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-person-badge-fill fa-2x text-gray-300"></i>
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
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Subjects</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboardData['total_subjects']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-book-fill fa-2x text-gray-300"></i>
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
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Classes</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $dashboardData['total_classes']; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-building fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Results -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Results</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>CA Marks</th>
                                        <th>Exam Marks</th>
                                        <th>Average</th>
                                        <th>Grade</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dashboardData['recent_results'] as $result): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($result['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                                        <td><?php echo $result['ca_marks'] ?? '-'; ?></td>
                                        <td><?php echo $result['exam_marks'] ?? '-'; ?></td>
                                        <td><?php echo $result['average_marks'] ?? '-'; ?></td>
                                        <td>
                                            <?php if ($result['letter_grade']): ?>
                                                <span class="badge bg-<?php echo getGradeColor($result['letter_grade']); ?>">
                                                    <?php echo $result['letter_grade']; ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($result['created_at']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php elseif ($user['role'] === 'principal'): ?>
                <!-- Principal Dashboard -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Division Statistics</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dashboardData['division_stats'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Division</th>
                                                    <th>Level</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($dashboardData['division_stats'] as $stat): ?>
                                                <tr>
                                                    <td><?php echo $stat['division']; ?></td>
                                                    <td><?php echo ucfirst($stat['level']); ?></td>
                                                    <td><?php echo $stat['count']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No division data available for current term.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Top Performers</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dashboardData['top_performers'])): ?>
                                    <div class="list-group">
                                        <?php foreach (array_slice($dashboardData['top_performers'], 0, 5) as $performer): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo htmlspecialchars($performer['student_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $performer['division']; ?></small>
                                            </div>
                                            <span class="badge bg-success rounded-pill"><?php echo $performer['total_points']; ?> pts</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No top performers data available.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Teacher Dashboard -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Your Assigned Subjects</h6>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($dashboardData['assigned_subjects'])): ?>
                                    <div class="row">
                                        <?php foreach ($dashboardData['assigned_subjects'] as $subject): ?>
                                        <div class="col-md-6 mb-3">
                                            <div class="card border-left-primary">
                                                <div class="card-body">
                                                    <h6 class="card-title"><?php echo htmlspecialchars($subject['name']); ?></h6>
                                                    <p class="card-text">
                                                        <small class="text-muted">
                                                            <?php echo ucfirst($subject['level']); ?> Level - <?php echo $subject['code']; ?>
                                                        </small>
                                                    </p>
                                                    <a href="results/enter.php?subject_id=<?php echo $subject['id']; ?>" class="btn btn-sm btn-primary">
                                                        Enter Results
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        No subjects assigned yet. Contact the administrator.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Results Progress</h6>
                            </div>
                            <div class="card-body">
                                <?php foreach ($dashboardData['results_progress'] as $progress): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="font-weight-bold"><?php echo htmlspecialchars($progress['subject']['code']); ?></small>
                                        <small class="text-muted"><?php echo $progress['entered']; ?>/<?php echo $progress['total']; ?></small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?php echo $progress['percentage']; ?>%" 
                                             aria-valuenow="<?php echo $progress['percentage']; ?>" 
                                             aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo $progress['percentage']; ?>% Complete</small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <?php if ($user['role'] === 'class_teacher' && isset($dashboardData['assigned_class'])): ?>
                        <div class="card shadow mt-3">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Your Class</h6>
                            </div>
                            <div class="card-body">
                                <h6><?php echo htmlspecialchars($dashboardData['assigned_class']['name']); ?></h6>
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="border-right">
                                            <div class="h5 font-weight-bold text-primary"><?php echo $dashboardData['class_stats']['total_students']; ?></div>
                                            <div class="text-xs text-uppercase text-muted">Total</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-right">
                                            <div class="h5 font-weight-bold text-info"><?php echo $dashboardData['class_stats']['male_students']; ?></div>
                                            <div class="text-xs text-uppercase text-muted">Male</div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="h5 font-weight-bold text-warning"><?php echo $dashboardData['class_stats']['female_students']; ?></div>
                                        <div class="text-xs text-uppercase text-muted">Female</div>
                                    </div>
                                </div>
                                <a href="students/class.php?class_id=<?php echo $dashboardData['assigned_class']['id']; ?>" class="btn btn-sm btn-outline-primary mt-2">
                                    View Class Students
                                </a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if (hasRole(['super_admin', 'principal'])): ?>
                        <div class="col-md-3 mb-2">
                            <a href="students/" class="btn btn-outline-primary btn-sm w-100">
                                <i class="bi bi-people me-1"></i>Manage Students
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (hasRole(['super_admin'])): ?>
                        <div class="col-md-3 mb-2">
                            <a href="teachers/" class="btn btn-outline-success btn-sm w-100">
                                <i class="bi bi-person-badge me-1"></i>Manage Teachers
                            </a>
                        </div>
                        <div class="col-md-3 mb-2">
                            <a href="subjects/" class="btn btn-outline-info btn-sm w-100">
                                <i class="bi bi-book me-1"></i>Manage Subjects
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3 mb-2">
                            <a href="results/" class="btn btn-outline-warning btn-sm w-100">
                                <i class="bi bi-clipboard-data me-1"></i>View Results
                            </a>
                        </div>
                        
                        <div class="col-md-3 mb-2">
                            <a href="reports/" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="bi bi-file-earmark-text me-1"></i>Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
