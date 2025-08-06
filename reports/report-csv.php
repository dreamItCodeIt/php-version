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

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_report_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

switch ($type) {
    case 'student_results':
        if ($student_id && $term_id && $academic_year_id) {
            $student = (new Student())->getById($student_id);
            $results = $resultObj->getStudentResults($student_id, $term_id, $academic_year_id);
            
            // CSV Headers
            fputcsv($output, ['Student Report']);
            fputcsv($output, ['Name', $student['name']]);
            fputcsv($output, ['Registration Number', $student['registration_number']]);
            fputcsv($output, ['Class', $student['class_name']]);
            fputcsv($output, []);
            fputcsv($output, ['Subject', 'Marks', 'Grade', 'Points', 'Remarks']);
            
            foreach ($results as $result) {
                fputcsv($output, [
                    $result['subject_name'],
                    $result['marks'],
                    $result['grade'],
                    $result['points'],
                    $result['remarks'] ?? ''
                ]);
            }
        }
        break;

    case 'class_performance':
        if ($class_id && $term_id && $academic_year_id) {
            $performanceData = $resultObj->getClassPerformance($class_id, $term_id, $academic_year_id);
            $classInfo = $resultObj->getClassInfo($class_id);
            
            // CSV Headers
            fputcsv($output, ['Class Performance Report']);
            fputcsv($output, ['Class', $classInfo['name']]);
            fputcsv($output, ['Stream', $classInfo['stream']]);
            fputcsv($output, []);
            fputcsv($output, ['Rank', 'Student Name', 'Registration Number', 'Total Points', 'Average Points', 'Division', 'Subjects']);
            
            $rank = 1;
            foreach ($performanceData as $student) {
                fputcsv($output, [
                    $rank,
                    $student['student_name'],
                    $student['registration_number'],
                    $student['total_points'],
                    number_format($student['average_points'], 2),
                    $student['division'],
                    $student['subjects_count']
                ]);
                $rank++;
            }
        }
        break;

    case 'subject_analysis':
        if ($subject_id && $term_id && $academic_year_id) {
            $analysisData = $resultObj->getSubjectAnalysis($subject_id, $term_id, $academic_year_id);
            $subjectInfo = $resultObj->getSubjectInfo($subject_id);
            
            // CSV Headers
            fputcsv($output, ['Subject Analysis Report']);
            fputcsv($output, ['Subject', $subjectInfo['name']]);
            fputcsv($output, ['Code', $subjectInfo['code']]);
            fputcsv($output, []);
            fputcsv($output, ['Statistics']);
            fputcsv($output, ['Total Students', $analysisData['statistics']['total_students']]);
            fputcsv($output, ['Average Score', number_format($analysisData['statistics']['average_score'], 2) . '%']);
            fputcsv($output, ['Pass Rate', number_format($analysisData['statistics']['pass_rate'], 2) . '%']);
            fputcsv($output, []);
            fputcsv($output, ['Rank', 'Student Name', 'Class', 'Marks', 'Grade', 'Points']);
            
            $rank = 1;
            foreach ($analysisData['student_results'] as $result) {
                fputcsv($output, [
                    $rank,
                    $result['student_name'],
                    $result['class_name'],
                    $result['marks'],
                    $result['grade'],
                    $result['points']
                ]);
                $rank++;
            }
        }
        break;

    case 'grade_distribution':
        if ($term_id && $academic_year_id) {
            $distributionData = $resultObj->getGradeDistribution($term_id, $academic_year_id);
            
            // CSV Headers
            fputcsv($output, ['Grade Distribution Report']);
            fputcsv($output, []);
            fputcsv($output, ['Overall Distribution']);
            fputcsv($output, ['Grade', 'Count', 'Percentage']);
            
            foreach ($distributionData['overall'] as $grade => $data) {
                fputcsv($output, [
                    $grade,
                    $data['count'],
                    number_format($data['percentage'], 1) . '%'
                ]);
            }
            
            fputcsv($output, []);
            fputcsv($output, ['Subject-wise Distribution']);
            fputcsv($output, ['Subject', 'A', 'B', 'C', 'D', 'E', 'F', 'Total', 'Pass Rate']);
            
            foreach ($distributionData['by_subject'] as $subject => $grades) {
                fputcsv($output, [
                    $subject,
                    $grades['A']['count'] ?? 0,
                    $grades['B']['count'] ?? 0,
                    $grades['C']['count'] ?? 0,
                    $grades['D']['count'] ?? 0,
                    $grades['E']['count'] ?? 0,
                    $grades['F']['count'] ?? 0,
                    $grades['total'],
                    number_format($grades['pass_rate'], 1) . '%'
                ]);
            }
        }
        break;

    case 'top_performers':
        if ($term_id && $academic_year_id) {
            $limit = $_GET['limit'] ?? 20;
            $topPerformers = $resultObj->getTopPerformers($term_id, $academic_year_id, $limit);
            
            // CSV Headers
            fputcsv($output, ['Top Performers Report']);
            fputcsv($output, []);
            fputcsv($output, ['Position', 'Student Name', 'Registration Number', 'Class', 'Stream', 'Total Points', 'Average Points', 'Division', 'Subjects']);
            
            $position = 1;
            foreach ($topPerformers as $student) {
                fputcsv($output, [
                    $position,
                    $student['student_name'],
                    $student['registration_number'],
                    $student['class_name'],
                    $student['stream'],
                    $student['total_points'],
                    number_format($student['average_points'], 2),
                    $student['division'],
                    $student['subjects_count']
                ]);
                $position++;
            }
        }
        break;

    default:
        fputcsv($output, ['Error: Invalid report type']);
        break;
}

fclose($output);
exit;
?>
