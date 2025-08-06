<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$report_type = $_GET['type'] ?? 'student';
$id = $_GET['id'] ?? null;
$academic_year_id = $_GET['academic_year_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;

if (!$id || !$academic_year_id || !$term_id) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
            .container { max-width: 100% !important; }
        }
        .school-header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .report-title {
            background-color: #f8f9fa;
            padding: 10px;
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="no-print mb-3">
            <button class="btn btn-primary" onclick="window.print()">Print Report</button>
            <button class="btn btn-secondary" onclick="window.close()">Close</button>
        </div>
        
        <div class="school-header">
            <h2>SCHOOL RESULTS MANAGEMENT SYSTEM</h2>
            <p>Academic Excellence Report</p>
            <p>Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
        </div>

        <?php
        switch ($report_type) {
            case 'student':
                include 'student_report.php';
                break;
            case 'class':
                include 'class_report.php';
                break;
            case 'subject':
                include 'subject_report.php';
                break;
            case 'term':
                include 'term_report.php';
                break;
            default:
                echo '<div class="alert alert-danger">Invalid report type</div>';
        }
        ?>
    </div>

    <script>
        // Auto print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        }
    </script>
</body>
</html>
