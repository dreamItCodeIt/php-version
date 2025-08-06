<?php
class Result {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT r.*, s.name as student_name, s.admission_no, sub.name as subject_name, 
                sub.code as subject_code, sub.level, t.name as teacher_name, term.name as term_name
                FROM results r 
                JOIN students s ON r.student_id = s.id 
                JOIN subjects sub ON r.subject_id = sub.id 
                JOIN users t ON r.teacher_id = t.id 
                JOIN terms term ON r.term_id = term.id 
                WHERE r.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getByStudent($studentId, $termId = null, $academicYearId = null) {
        $sql = "SELECT r.*, sub.name as subject_name, sub.code as subject_code, sub.level, 
                t.name as teacher_name, term.name as term_name
                FROM results r 
                JOIN subjects sub ON r.subject_id = sub.id 
                JOIN users t ON r.teacher_id = t.id 
                JOIN terms term ON r.term_id = term.id 
                WHERE r.student_id = ?";
        $params = [$studentId];
        
        if ($termId) {
            $sql .= " AND r.term_id = ?";
            $params[] = $termId;
        }
        
        if ($academicYearId) {
            $sql .= " AND r.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        $sql .= " ORDER BY sub.name";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getBySubject($subjectId, $termId, $academicYearId) {
        $sql = "SELECT r.*, s.name as student_name, s.admission_no, s.current_form,
                t.name as teacher_name, term.name as term_name
                FROM results r 
                JOIN students s ON r.student_id = s.id 
                JOIN users t ON r.teacher_id = t.id 
                JOIN terms term ON r.term_id = term.id 
                WHERE r.subject_id = ? AND r.term_id = ? AND r.academic_year_id = ?
                ORDER BY r.average_marks DESC";
        return $this->db->fetchAll($sql, [$subjectId, $termId, $academicYearId]);
    }
    
    public function getByTeacher($teacherId, $termId = null, $academicYearId = null) {
        $sql = "SELECT r.*, s.name as student_name, s.admission_no, sub.name as subject_name, 
                sub.code as subject_code, term.name as term_name
                FROM results r 
                JOIN students s ON r.student_id = s.id 
                JOIN subjects sub ON r.subject_id = sub.id 
                JOIN terms term ON r.term_id = term.id 
                WHERE r.teacher_id = ?";
        $params = [$teacherId];
        
        if ($termId) {
            $sql .= " AND r.term_id = ?";
            $params[] = $termId;
        }
        
        if ($academicYearId) {
            $sql .= " AND r.academic_year_id = ?";
            $params[] = $academicYearId;
        }
        
        $sql .= " ORDER BY sub.name, s.name";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function createOrUpdate($data) {
        // Check if result already exists
        $existing = $this->findByStudentSubjectTerm($data['student_id'], $data['subject_id'], $data['term_id'], $data['academic_year_id']);
        
        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->create($data);
        }
    }
    
    public function create($data) {
        // Calculate average, grade, and points
        $calculated = $this->calculateGradeAndPoints($data['ca_marks'], $data['exam_marks'], $data['level']);
        
        $sql = "INSERT INTO results (student_id, subject_id, term_id, academic_year_id, ca_marks, exam_marks, 
                average_marks, letter_grade, points, teacher_id, entered_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['student_id'],
                $data['subject_id'],
                $data['term_id'],
                $data['academic_year_id'],
                $data['ca_marks'],
                $data['exam_marks'],
                $calculated['average'],
                $calculated['grade'],
                $calculated['points'],
                $data['teacher_id'],
                date('Y-m-d H:i:s')
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function update($id, $data) {
        // Calculate average, grade, and points
        $calculated = $this->calculateGradeAndPoints($data['ca_marks'], $data['exam_marks'], $data['level']);
        
        $sql = "UPDATE results SET ca_marks = ?, exam_marks = ?, average_marks = ?, letter_grade = ?, 
                points = ?, teacher_id = ?, entered_at = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $this->db->query($sql, [
                $data['ca_marks'],
                $data['exam_marks'],
                $calculated['average'],
                $calculated['grade'],
                $calculated['points'],
                $data['teacher_id'],
                date('Y-m-d H:i:s'),
                $id
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function delete($id) {
        $sql = "DELETE FROM results WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function findByStudentSubjectTerm($studentId, $subjectId, $termId, $academicYearId) {
        $sql = "SELECT * FROM results WHERE student_id = ? AND subject_id = ? AND term_id = ? AND academic_year_id = ?";
        return $this->db->fetchOne($sql, [$studentId, $subjectId, $termId, $academicYearId]);
    }
    
    private function calculateGradeAndPoints($caMarks, $examMarks, $level) {
        if ($caMarks === null || $examMarks === null) {
            return ['average' => null, 'grade' => null, 'points' => null];
        }
        
        $average = ($caMarks + $examMarks) / 2;
        $gradeData = calculateGrade($average, $level);
        
        return [
            'average' => round($average, 2),
            'grade' => $gradeData['grade'],
            'points' => $gradeData['points']
        ];
    }
    
    public function getSubjectStatistics($subjectId, $termId, $academicYearId) {
        $sql = "SELECT 
                    COUNT(*) as total_students,
                    AVG(average_marks) as average_score,
                    MAX(average_marks) as highest_score,
                    MIN(average_marks) as lowest_score,
                    letter_grade,
                    COUNT(*) as grade_count
                FROM results 
                WHERE subject_id = ? AND term_id = ? AND academic_year_id = ? AND average_marks IS NOT NULL
                GROUP BY letter_grade";
        
        $gradeStats = $this->db->fetchAll($sql, [$subjectId, $termId, $academicYearId]);
        
        $sql = "SELECT 
                    COUNT(*) as total_students,
                    AVG(average_marks) as average_score,
                    MAX(average_marks) as highest_score,
                    MIN(average_marks) as lowest_score
                FROM results 
                WHERE subject_id = ? AND term_id = ? AND academic_year_id = ? AND average_marks IS NOT NULL";
        
        $overallStats = $this->db->fetchOne($sql, [$subjectId, $termId, $academicYearId]);
        
        return [
            'overall' => $overallStats,
            'grades' => $gradeStats
        ];
    }
}
?>
