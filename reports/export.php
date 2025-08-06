<?php
require_once '../config/config.php';
require_once '../classes/Auth.php';
require_once '../classes/Result.php';
require_once '../classes/Student.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$result = new Result();
$student = new Student();

$export_type = $_GET['type'] ?? 'csv';
$report_type = $_GET['report'] ?? 'students';
$academic_year_id = $_GET['academic_year_id'] ?? null;
$term_id = $_GET['term_id'] ?? null;

if (!$academic_year_id || !$term_id) {
    header('Location: index.php');
    exit;
}

// Set headers for download
if ($export_type === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $report_type . '_report.csv"');
} else {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $report_type . '_report.json"');
}

$output = fopen('php://output', 'w');

switch ($report_type) {
    case 'students':
        $data = $student->getAllWithResults($academic_year_id, $term_id);
        if ($export_type === 'csv') {
            fputcsv($output, ['Name', 'Admission Number', 'Class', 'Form', 'Level', 'Total Points', 'Average', 'Division']);
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['first_name'] . ' ' . $row['last_name'],
                    $row['admission_number'],
                    $row['class_name'],
                    $row['form'],
                    $row['level'],
                    $row['total_points'],
                    number_format($row['average_points'], 2),
                    $row['division']
                ]);
            }
        } else {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        break;
        
    case 'results':
        $data = $result->getAllResults($academic_year_id, $term_id);
        if ($export_type === 'csv') {
            fputcsv($output, ['Student Name', 'Admission Number', 'Subject', 'Score', 'Grade', 'Points']);
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['student_name'],
                    $row['admission_number'],
                    $row['subject_name'],
                    $row['score'],
                    $row['grade'],
                    $row['points']
                ]);
            }
        } else {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        break;
        
    case 'summary':
        $data = $result->getTermSummary($academic_year_id, $term_id);
        if ($export_type === 'csv') {
            fputcsv($output, ['Class', 'Total Students', 'Average Score', 'Pass Rate', 'Division I', 'Division II', 'Division III', 'Division IV']);
            foreach ($data as $row) {
                fputcsv($output, [
                    $row['class_name'],
                    $row['total_students'],
                    number_format($row['average_score'], 2),
                    number_format($row['pass_rate'], 2) . '%',
                    $row['division_1'],
                    $row['division_2'],
                    $row['division_3'],
                    $row['division_4']
                ]);
            }
        } else {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }
        break;
}

fclose($output);
?>
