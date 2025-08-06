<?php
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';

// Check authentication
if (!isLoggedIn()) {
    redirect('login.php');
}

$type = $_GET['type'] ?? '';
$term_id = $_GET['term_id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;
$subject_id = $_GET['subject_id'] ?? null;
$student_id = $_GET['student_id'] ?? null;

$resultObj = new Result();

// Simple PDF generation using HTML and CSS
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.pdf"');

// Start output buffering
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst(str_replace('_', ' ', $type)); ?> Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 16px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .results-table th,
        .results-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .results-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .grade-a { background-color: #d4edda; }
        .grade-b { background-color: #d1ecf1; }
        .grade-c { background-color: #fff3cd; }
        .grade-d { background-color: #f8d7da; }
        .grade-e { background-color: #f5c6cb; }
        .grade-f { background-color: #f1b0b7; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name"><?php echo APP_NAME; ?></div>
        <div class="report-title"><?php echo ucfirst(str_replace('_', ' ', $type)); ?> Report</div>
        <div>Generated on: <?php echo date('F j, Y'); ?></div>
    </div>

    <?php
    switch ($type) {
        case 'student_results':
            if ($student_id && $term_id && $academic_year_id) {
                $student = (new Student())->getById($student_id);
                $results = $resultObj->getStudentResults($student_id, $term_id, $academic_year_id);
                $summary = $resultObj->getStudentSummary($student_id, $term_id, $academic_year_id);
                ?>
                <div class="info-section">
                    <h3>Student Information</h3>
                    <table class="info-table">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><strong>Registration Number:</strong></td>
                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Class:</strong></td>
                            <td><?php echo htmlspecialchars($student['class_name']); ?></td>
                            <td><strong>Stream:</strong></td>
                            <td><?php echo htmlspecialchars($student['stream']); ?></td>
                        </tr>
                    </table>
                </div>

                <h3>Academic Results</h3>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Points</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($results as $result): ?>
                        <tr class="grade-<?php echo strtolower($result['grade']); ?>">
                            <td><?php echo htmlspecialchars($result['subject_name']); ?></td>
                            <td><?php echo $result['marks']; ?></td>
                            <td><?php echo $result['grade']; ?></td>
                            <td><?php echo $result['points']; ?></td>
                            <td><?php echo htmlspecialchars($result['remarks'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if (!empty($summary)): ?>
                <div class="info-section">
                    <h3>Summary</h3>
                    <table class="info-table">
                        <tr>
                            <td><strong>Total Points:</strong></td>
                            <td><?php echo $summary['total_points']; ?></td>
                            <td><strong>Average Points:</strong></td>
                            <td><?php echo number_format($summary['average_points'], 2); ?></td>
                            <td><strong>Division:</strong></td>
                            <td><?php echo $summary['division']; ?></td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>
                <?php
            }
            break;

        case 'class_performance':
            if ($class_id && $term_id && $academic_year_id) {
                $performanceData = $resultObj->getClassPerformance($class_id, $term_id, $academic_year_id);
                $classInfo = $resultObj->getClassInfo($class_id);
                ?>
                <div class="info-section">
                    <h3>Class Information</h3>
                    <table class="info-table">
                        <tr>
                            <td><strong>Class:</strong></td>
                            <td><?php echo htmlspecialchars($classInfo['name']); ?></td>
                            <td><strong>Stream:</strong></td>
                            <td><?php echo htmlspecialchars($classInfo['stream']); ?></td>
                            <td><strong>Total Students:</strong></td>
                            <td><?php echo count($performanceData); ?></td>
                        </tr>
                    </table>
                </div>

                <h3>Student Performance</h3>
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student Name</th>
                            <th>Registration Number</th>
                            <th>Total Points</th>
                            <th>Average Points</th>
                            <th>Division</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $rank = 1;
                        foreach ($performanceData as $student): 
                        ?>
                        <tr>
                            <td><?php echo $rank; ?></td>
                            <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                            <td><?php echo $student['total_points']; ?></td>
                            <td><?php echo number_format($student['average_points'], 2); ?></td>
                            <td><?php echo $student['division']; ?></td>
                        </tr>
                        <?php 
                        $rank++;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
                <?php
            }
            break;

        default:
            echo '<p>Report type not supported for PDF export.</p>';
            break;
    }
    ?>

    <div class="footer">
        <p>This report was generated automatically by <?php echo APP_NAME; ?> on <?php echo date('F j, Y \a\t g:i A'); ?></p>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// For a simple implementation, we'll output HTML that can be printed as PDF
// In a production environment, you would use a library like TCPDF or mPDF
echo $html;
exit;
?>
