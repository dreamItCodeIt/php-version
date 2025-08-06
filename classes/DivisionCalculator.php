<?php
class DivisionCalculator {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function calculateForStudent($studentId, $termId, $academicYearId) {
        // Get student info
        $student = $this->db->fetchOne("SELECT * FROM students WHERE id = ?", [$studentId]);
        if (!$student) return false;
        
        $level = $student['current_form'] <= 4 ? 'ordinary' : 'advanced';
        
        // Get all results for the student in this term
        $sql = "SELECT r.*, s.name as subject_name FROM results r 
                JOIN subjects s ON r.subject_id = s.id 
                WHERE r.student_id = ? AND r.term_id = ? AND r.academic_year_id = ? 
                AND r.points IS NOT NULL
                ORDER BY r.points ASC";
        
        $results = $this->db->fetchAll($sql, [$studentId, $termId, $academicYearId]);
        
        if (empty($results)) return false;
        
        if ($level === 'ordinary') {
            return $this->calculateOrdinaryDivision($studentId, $termId, $academicYearId, $results);
        } else {
            return $this->calculateAdvancedDivision($studentId, $termId, $academicYearId, $results);
        }
    }
    
    private function calculateOrdinaryDivision($studentId, $termId, $academicYearId, $results) {
        // For O-Level, use best 7 subjects
        $bestResults = array_slice($results, 0, 7);
        
        if (count($bestResults) < 7) {
            return false; // Not enough subjects
        }
        
        $totalPoints = array_sum(array_column($bestResults, 'points'));
        $division = $this->getOrdinaryDivision($totalPoints);
        
        return $this->saveDivision($studentId, $termId, $academicYearId, 'ordinary', $totalPoints, $division, $bestResults);
    }
    
    private function calculateAdvancedDivision($studentId, $termId, $academicYearId, $results) {
        // For A-Level, use all subjects (should be 4)
        if (count($results) < 4) {
            return false; // Not enough subjects
        }
        
        $totalPoints = array_sum(array_column($results, 'points'));
        $division = $this->getAdvancedDivision($totalPoints);
        
        return $this->saveDivision($studentId, $termId, $academicYearId, 'advanced', $totalPoints, $division, $results);
    }
    
    private function getOrdinaryDivision($totalPoints) {
        if ($totalPoints >= 7 && $totalPoints <= 17) return 'Division I';
        if ($totalPoints >= 18 && $totalPoints <= 21) return 'Division II';
        if ($totalPoints >= 22 && $totalPoints <= 25) return 'Division III';
        if ($totalPoints >= 26 && $totalPoints <= 33) return 'Division IV';
        return 'Division 0';
    }
    
    private function getAdvancedDivision($totalPoints) {
        if ($totalPoints >= 3 && $totalPoints <= 9) return 'Division I';
        if ($totalPoints >= 10 && $totalPoints <= 12) return 'Division II';
        if ($totalPoints >= 13 && $totalPoints <= 17) return 'Division III';
        if ($totalPoints >= 18 && $totalPoints <= 19) return 'Division IV';
        return 'Division 0';
    }
    
    private function saveDivision($studentId, $termId, $academicYearId, $level, $totalPoints, $division, $results) {
        $subjectsUsed = array_map(function($result) {
            return [
                'subject_id' => $result['subject_id'],
                'subject_name' => $result['subject_name'],
                'points' => $result['points'],
                'grade' => $result['letter_grade']
            ];
        }, $results);
        
        // Check if division already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM student_divisions WHERE student_id = ? AND term_id = ? AND academic_year_id = ?",
            [$studentId, $termId, $academicYearId]
        );
        
        if ($existing) {
            $sql = "UPDATE student_divisions SET level = ?, total_points = ?, division = ?, 
                    subjects_used = ?, subjects_count = ?, calculated_at = ?, updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ?";
            $params = [
                $level, $totalPoints, $division, json_encode($subjectsUsed), 
                count($results), date('Y-m-d H:i:s'), $existing['id']
            ];
        } else {
            $sql = "INSERT INTO student_divisions (student_id, term_id, academic_year_id, level, 
                    total_points, division, subjects_used, subjects_count, calculated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $studentId, $termId, $academicYearId, $level, $totalPoints, 
                $division, json_encode($subjectsUsed), count($results), date('Y-m-d H:i:s')
            ];
        }
        
        try {
            $this->db->query($sql, $params);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function calculateForAllStudents($termId, $academicYearId) {
        $students = $this->db->fetchAll("SELECT id FROM students WHERE status = 'active'");
        
        $calculated = 0;
        foreach ($students as $student) {
            if ($this->calculateForStudent($student['id'], $termId, $academicYearId)) {
                $calculated++;
            }
        }
        
        return $calculated;
    }
    
    public function getDivisionStatistics($termId, $academicYearId, $level = null) {
        $sql = "SELECT division, level, COUNT(*) as count FROM student_divisions 
                WHERE term_id = ? AND academic_year_id = ?";
        $params = [$termId, $academicYearId];
        
        if ($level) {
            $sql .= " AND level = ?";
            $params[] = $level;
        }
        
        $sql .= " GROUP BY division, level ORDER BY 
                  CASE division 
                    WHEN 'Division I' THEN 1 
                    WHEN 'Division II' THEN 2 
                    WHEN 'Division III' THEN 3 
                    WHEN 'Division IV' THEN 4 
                    ELSE 5 
                  END";
        
        return $this->db->fetchAll($sql, $params);
    }
}
?>
