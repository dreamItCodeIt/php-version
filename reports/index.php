<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$db = Database::getInstance();

// Get current academic year and term
$currentYear = $db->fetchOne("SELECT * FROM academic_years WHERE is_current = 1");
$currentTerm = $db->fetchOne("SELECT * FROM terms WHERE is_current = 1");

// Get available terms and years for filtering
$academicYears = $db->fetchAll("SELECT * FROM academic_years ORDER BY year DESC");
$terms = $db->fetchAll("SELECT * FROM terms ORDER BY name");

$pageTitle = 'Reports';
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-file-earmark-text me-2"></i>Reports
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <span class="badge bg-primary me-2">
                            <?php echo $currentTerm['name'] ?? 'No Current Term'; ?>
                        </span>
                        <span class="badge bg-secondary">
                            <?php echo $currentYear['year'] ?? 'No Current Year'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Report Categories -->
            <div class="row mb-4">
                <!-- Student Reports -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-person-lines-fill text-primary" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Student Reports</h5>
                                    <p class="card-text text-muted">Individual student performance reports</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="student-report.php" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-file-person me-1"></i>Student Report Card
                                </a>
                                <a href="student-progress.php" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-graph-up me-1"></i>Progress Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Class Reports -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-people-fill text-success" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Class Reports</h5>
                                    <p class="card-text text-muted">Class performance and statistics</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="class-performance.php" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-bar-chart me-1"></i>Class Performance
                                </a>
                                <a href="class-ranking.php" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-trophy me-1"></i>Class Ranking
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subject Reports -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-book-fill text-info" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Subject Reports</h5>
                                    <p class="card-text text-muted">Subject-wise analysis and statistics</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="subject-analysis.php" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-graph-down me-1"></i>Subject Analysis
                                </a>
                                <a href="grade-distribution.php" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-pie-chart me-1"></i>Grade Distribution
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Division Reports -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-award-fill text-warning" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Division Reports</h5>
                                    <p class="card-text text-muted">Division statistics and analysis</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="division-statistics.php" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-clipboard-data me-1"></i>Division Statistics
                                </a>
                                <a href="top-performers.php" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-star me-1"></i>Top Performers
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- School Reports -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-building text-secondary" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">School Reports</h5>
                                    <p class="card-text text-muted">Overall school performance</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="school-summary.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-clipboard-check me-1"></i>School Summary
                                </a>
                                <a href="annual-report.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-calendar-check me-1"></i>Annual Report
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Options -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="bi bi-download text-danger" style="font-size: 2rem;"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="card-title mb-0">Export Data</h5>
                                    <p class="card-text text-muted">Export data in various formats</p>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="export-csv.php" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-filetype-csv me-1"></i>Export to CSV
                                </a>
                                <a href="export-pdf.php" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-filetype-pdf me-1"></i>Export to PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Statistics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Quick Statistics - <?php echo $currentTerm['name'] ?? 'Current Term'; ?></h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <?php
                        // Get quick stats
                        $totalStudents = $db->fetchOne("SELECT COUNT(*) as count FROM students WHERE status = 'active'")['count'];
                        $totalResults = $db->fetchOne("SELECT COUNT(*) as count FROM results WHERE term_id = ? AND average_marks IS NOT NULL", [$currentTerm['id'] ?? 0])['count'];
                        $divisionI = $db->fetchOne("SELECT COUNT(*) as count FROM student_divisions WHERE term_id = ? AND division = 'Division I'", [$currentTerm['id'] ?? 0])['count'];
                        $averageScore = $db->fetchOne("SELECT AVG(average_marks) as avg FROM results WHERE term_id = ? AND average_marks IS NOT NULL", [$currentTerm['id'] ?? 0])['avg'];
                        ?>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="h4 font-weight-bold text-primary"><?php echo $totalStudents; ?></div>
                                    <div class="text-xs text-uppercase text-muted">Total Students</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="h4 font-weight-bold text-success"><?php echo $totalResults; ?></div>
                                    <div class="text-xs text-uppercase text-muted">Results Entered</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="h4 font-weight-bold text-warning"><?php echo $divisionI; ?></div>
                                    <div class="text-xs text-uppercase text-muted">Division I</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="h4 font-weight-bold text-info"><?php echo number_format($averageScore ?? 0, 1); ?>%</div>
                                    <div class="text-xs text-uppercase text-muted">Average Score</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
