<?php
require_once '../config/config.php';
requireLogin();
requireRole(['teacher', 'class_teacher']);

$user = getCurrentUser();
$db = Database::getInstance();
$resultObj = new Result();
$studentObj = new Student();
$subjectObj = new Subject();

// Get current academic year and term
$currentYear = $db->fetchOne("SELECT * FROM academic_years WHERE is_current = 1");
$currentTerm = $db->fetchOne("SELECT * FROM terms WHERE is_current = 1");

if (!$currentYear || !$currentTerm) {
    redirect('/dashboard.php?error=no_current_term');
}

$subjectId = $_GET['subject_id'] ?? null;
if (!$subjectId) {
    redirect('/results/?error=no_subject');
}

$subject = $subjectObj->findById($subjectId);
if (!$subject) {
    redirect('/results/?error=subject_not_found');
}

// Check if user can enter results for this subject
$userObj = new User();
$teacherSubjects = $userObj->getTeacherSubjects($user['id'], $currentYear['id']);
$canEnter = false;
foreach ($teacherSubjects as $ts) {
    if ($ts['id'] == $subjectId) {
        $canEnter = true;
        break;
    }
}

if (!$canEnter) {
    redirect('/results/?error=unauthorized');
}

// Get students enrolled in this subject
$students = $studentObj->getBySubject($subjectId, $currentYear['id']);

// Get existing results for these students
$existingResults = [];
foreach ($students as $student) {
    $result = $resultObj->findByStudentSubjectTerm($student['id'], $subjectId, $currentTerm['id'], $currentYear['id']);
    if ($result) {
        $existingResults[$student['id']] = $result;
    }
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $results = $_POST['results'] ?? [];
        $saved = 0;
        $errors = 0;
        
        $db->beginTransaction();
        try {
            foreach ($results as $studentId => $data) {
                $caMarks = !empty($data['ca_marks']) ? (int)$data['ca_marks'] : null;
                $examMarks = !empty($data['exam_marks']) ? (int)$data['exam_marks'] : null;
                
                if ($caMarks !== null || $examMarks !== null) {
                    $resultData = [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'term_id' => $currentTerm['id'],
                        'academic_year_id' => $currentYear['id'],
                        'ca_marks' => $caMarks,
                        'exam_marks' => $examMarks,
                        'teacher_id' => $user['id'],
                        'level' => $subject['level']
                    ];
                    
                    if ($resultObj->createOrUpdate($resultData)) {
                        $saved++;
                        
                        // Calculate division if both marks are present
                        if ($caMarks !== null && $examMarks !== null) {
                            $calculator = new DivisionCalculator();
                            $calculator->calculateForStudent($studentId, $currentTerm['id'], $currentYear['id']);
                        }
                    } else {
                        $errors++;
                    }
                }
            }
            
            $db->commit();
            
            if ($saved > 0) {
                $message = "Successfully saved results for {$saved} student(s).";
                if ($errors > 0) {
                    $message .= " {$errors} result(s) failed to save.";
                }
                
                // Refresh existing results
                $existingResults = [];
                foreach ($students as $student) {
                    $result = $resultObj->findByStudentSubjectTerm($student['id'], $subjectId, $currentTerm['id'], $currentYear['id']);
                    if ($result) {
                        $existingResults[$student['id']] = $result;
                    }
                }
            } else {
                $error = 'No results were saved. Please check your input.';
            }
            
        } catch (Exception $e) {
            $db->rollback();
            $error = 'An error occurred while saving results. Please try again.';
        }
    }
}

$pageTitle = 'Enter Results - ' . $subject['name'];
include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="bi bi-clipboard-data me-2"></i>Enter Results
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="../results/" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Results
                    </a>
                </div>
            </div>

            <!-- Subject Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="card-title"><?php echo htmlspecialchars($subject['name']); ?></h5>
                            <p class="card-text">
                                <span class="badge bg-primary"><?php echo $subject['code']; ?></span>
                                <span class="badge bg-secondary"><?php echo ucfirst($subject['level']); ?> Level</span>
                                <span class="badge bg-info"><?php echo $currentTerm['name']; ?></span>
                                <span class="badge bg-success"><?php echo $currentYear['year']; ?></span>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-1"><strong>Total Students:</strong> <?php echo count($students); ?></p>
                            <p class="mb-0"><strong>Results Entered:</strong> <?php echo count($existingResults); ?></p>
                        </div>
                    </div>
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

            <!-- Results Entry Form -->
            <?php if (!empty($students)): ?>
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Student Results Entry</h6>
                    <small class="text-muted">Enter CA marks and Exam marks for each student (0-100). Average and grades will be calculated automatically.</small>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="resultsForm">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Admission No.</th>
                                        <th>Form</th>
                                        <th>CA Marks</th>
                                        <th>Exam Marks</th>
                                        <th>Average</th>
                                        <th>Grade</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                    <?php $existing = $existingResults[$student['id']] ?? null; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo ucfirst($student['gender']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($student['admission_no']); ?></td>
                                        <td>Form <?php echo $student['current_form']; ?></td>
                                        <td>
                                            <input type="number" 
                                                   name="results[<?php echo $student['id']; ?>][ca_marks]" 
                                                   class="form-control form-control-sm ca-marks" 
                                                   min="0" max="100" 
                                                   value="<?php echo $existing['ca_marks'] ?? ''; ?>"
                                                   data-student-id="<?php echo $student['id']; ?>"
                                                   placeholder="0-100">
                                        </td>
                                        <td>
                                            <input type="number" 
                                                   name="results[<?php echo $student['id']; ?>][exam_marks]" 
                                                   class="form-control form-control-sm exam-marks" 
                                                   min="0" max="100" 
                                                   value="<?php echo $existing['exam_marks'] ?? ''; ?>"
                                                   data-student-id="<?php echo $student['id']; ?>"
                                                   placeholder="0-100">
                                        </td>
                                        <td>
                                            <span class="average-display" data-student-id="<?php echo $student['id']; ?>">
                                                <?php echo $existing['average_marks'] ?? '-'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="grade-display" data-student-id="<?php echo $student['id']; ?>">
                                                <?php if ($existing && $existing['letter_grade']): ?>
                                                    <span class="badge bg-<?php echo getGradeColor($existing['letter_grade']); ?>">
                                                        <?php echo $existing['letter_grade']; ?>
                                                    </span>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearAllResults()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Clear All
                                </button>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i>Save Results
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No students are enrolled in this subject for the current academic year.
            </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
const subjectLevel = '<?php echo $subject['level']; ?>';

function calculateGrade(average, level) {
    if (level === 'ordinary') {
        if (average >= 75) return { grade: 'A', color: 'success' };
        if (average >= 65) return { grade: 'B', color: 'primary' };
        if (average >= 45) return { grade: 'C', color: 'warning' };
        if (average >= 30) return { grade: 'D', color: 'secondary' };
        return { grade: 'F', color: 'danger' };
    } else {
        if (average >= 80) return { grade: 'A', color: 'success' };
        if (average >= 70) return { grade: 'B', color: 'primary' };
        if (average >= 60) return { grade: 'C', color: 'warning' };
        if (average >= 50) return { grade: 'D', color: 'secondary' };
        if (average >= 40) return { grade: 'E', color: 'danger' };
        return { grade: 'F', color: 'danger' };
    }
}

function updateCalculations(studentId) {
    const caInput = document.querySelector(`input[name="results[${studentId}][ca_marks]"]`);
    const examInput = document.querySelector(`input[name="results[${studentId}][exam_marks]"]`);
    const averageDisplay = document.querySelector(`span[data-student-id="${studentId}"].average-display`);
    const gradeDisplay = document.querySelector(`span[data-student-id="${studentId}"].grade-display`);
    
    const caMarks = parseFloat(caInput.value) || 0;
    const examMarks = parseFloat(examInput.value) || 0;
    
    if (caMarks > 0 || examMarks > 0) {
        const average = (caMarks + examMarks) / 2;
        const gradeInfo = calculateGrade(average, subjectLevel);
        
        averageDisplay.textContent = average.toFixed(1);
        gradeDisplay.innerHTML = `<span class="badge bg-${gradeInfo.color}">${gradeInfo.grade}</span>`;
    } else {
        averageDisplay.textContent = '-';
        gradeDisplay.textContent = '-';
    }
}

function clearAllResults() {
    if (confirm('Are you sure you want to clear all entered results?')) {
        document.querySelectorAll('.ca-marks, .exam-marks').forEach(input => {
            input.value = '';
            updateCalculations(input.dataset.studentId);
        });
    }
}

// Add event listeners for real-time calculation
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ca-marks, .exam-marks').forEach(input => {
        input.addEventListener('input', function() {
            updateCalculations(this.dataset.studentId);
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
