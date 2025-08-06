<?php
require_once '../config/config.php';
requireLogin();

$user = getCurrentUser();
$db = Database::getInstance();
$resultObj = new Result();
$subjectObj = new Subject();

// Get current academic year and term
$currentYear = $db->fetchOne("SELECT * FROM academic_years WHERE is_current = 1");
$currentTerm = $db->fetchOne("SELECT * FROM terms WHERE is_current = 1");

$subjectId = $_GET['subject_id'] ?? null;
$termId = $_GET['term_id'] ?? $currentTerm['id'] ?? null;

if (!$subjectId) {
    redirect('/results/?error=no_subject');
}

$subject = $subjectObj->findById($subjectId);
if (!$subject) {
    redirect('/results/?error=subject_not_found');
}

// Check permissions for teachers
if (hasRole(['teacher', 'class_teacher'])) {
    $userObj = new User();
    $teacherSubjects = $userObj->getTeacherSubjects($user['id'], $currentYear['id']);
    $canView = false;
    foreach ($teacherSubjects as $ts) {
        if ($ts['id'] == $subjectId) {
            $canView = true;
            break;
        }
    }
    
    if (!$canView) {
        redirect('/results/?error=unauthorized');
    }
}

// Get results for this subject
$results = $resultObj->getBySubject($subjectId, $termId, $currentYear['id']);

// Get statistics
$statistics = $resultObj->getSubjectStatistics($subjectId, $termId, $currentYear['id']);

$pageTitle = 'View Results - ' . $subject['name'];
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-eye me-2"></i>View Results
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="../results/" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left me-1"></i>Back to Results
                    </a>
                    <?php if (hasRole(['teacher', 'class_teacher'])): ?>
                    <a href="enter.php?subject_id=<?php echo $subjectId; ?>" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i>Enter Results
                    </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Subject Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="card-title"><?php echo htmlspecialchars($subject['name']); ?></h5>
                            <p class="card-text">
                                <span class="badge bg-primary"><?php echo $subject['code']; ?></span>
                                <span class="badge bg-secondary"><?php echo ucfirst($subject['level']); ?> Level</span>
                                <span class="badge bg-info"><?php echo $currentTerm['name'] ?? 'Current Term'; ?></span>
                                <span class="badge bg-success"><?php echo $currentYear['year'] ?? 'Current Year'; ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($statistics['overall']): ?>
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="h6 font-weight-bold text-primary"><?php echo $statistics['overall']['total_students']; ?></div>
                                    <div class="text-xs text-uppercase text-muted">Students</div>
                                </div>
                                <div class="col-3">
                                    <div class="h6 font-weight-bold text-success"><?php echo number_format($statistics['overall']['average_score'], 1); ?>%</div>
                                    <div class="text-xs text-uppercase text-muted">Average</div>
                                </div>
                                <div class="col-3">
                                    <div class="h6 font-weight-bold text-info"><?php echo $statistics['overall']['highest_score']; ?>%</div>
                                    <div class="text-xs text-uppercase text-muted">Highest</div>
                                </div>
                                <div class="col-3">
                                    <div class="h6 font-weight-bold text-warning"><?php echo $statistics['overall']['lowest_score']; ?>%</div>
                                    <div class="text-xs text-uppercase text-muted">Lowest</div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade Distribution -->
            <?php if (!empty($statistics['grades'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Grade Distribution</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($statistics['grades'] as $grade): ?>
                        <div class="col-md-2 col-sm-4 mb-3 text-center">
                            <div class="card border-0 bg-light">
                                <div class="card-body py-2">
                                    <div class="h4 font-weight-bold text-<?php echo getGradeColor($grade['letter_grade']); ?>">
                                        <?php echo $grade['grade_count']; ?>
                                    </div>
                                    <div class="text-xs text-uppercase text-muted">Grade <?php echo $grade['letter_grade']; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Results Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Student Results</h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>Print
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="exportToCSV()">
                            <i class="bi bi-download me-1"></i>Export CSV
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($results)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="resultsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Rank</th>
                                    <th>Student Name</th>
                                    <th>Admission No.</th>
                                    <th>Form</th>
                                    <th>CA Marks</th>
                                    <th>Exam Marks</th>
                                    <th>Average</th>
                                    <th>Grade</th>
                                    <th>Points</th>
                                    <th>Teacher</th>
                                    <th>Date Entered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $index => $result): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($result['student_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($result['admission_no']); ?></td>
                                    <td>Form <?php echo $result['current_form']; ?></td>
                                    <td><?php echo $result['ca_marks'] ?? '-'; ?></td>
                                    <td><?php echo $result['exam_marks'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($result['average_marks']): ?>
                                            <strong><?php echo number_format($result['average_marks'], 1); ?>%</strong>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($result['letter_grade']): ?>
                                            <span class="badge bg-<?php echo getGradeColor($result['letter_grade']); ?>">
                                                <?php echo $result['letter_grade']; ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $result['points'] ?? '-'; ?></td>
                                    <td>
                                        <small><?php echo htmlspecialchars($result['teacher_name']); ?></small>
                                    </td>
                                    <td>
                                        <small><?php echo $result['entered_at'] ? formatDate($result['entered_at']) : '-'; ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        No results have been entered for this subject yet.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
function exportToCSV() {
    const table = document.getElementById('resultsTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cols = row.querySelectorAll('td, th');
        let csvRow = [];
        
        for (let j = 0; j < cols.length; j++) {
            let cellText = cols[j].innerText.replace(/"/g, '""');
            csvRow.push('"' + cellText + '"');
        }
        
        csv.push(csvRow.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', '<?php echo $subject['code']; ?>_results.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}
</script>

<?php include '../includes/footer.php'; ?>
