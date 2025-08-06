<?php
class Subject {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM subjects WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getAll($level = null, $status = 'active') {
        $sql = "SELECT * FROM subjects WHERE status = ?";
        $params = [$status];
        
        if ($level) {
            $sql .= " AND level = ?";
            $params[] = $level;
        }
        
        $sql .= " ORDER BY name";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getOrdinaryLevel() {
        return $this->getAll('ordinary');
    }
    
    public function getAdvancedLevel() {
        return $this->getAll('advanced');
    }
    
    public function create($data) {
        $sql = "INSERT INTO subjects (name, code, level, status) VALUES (?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['code'],
                $data['level'],
                'active'
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function update($id, $data) {
        $sql = "UPDATE subjects SET name = ?, code = ?, level = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['code'],
                $data['level'],
                $id
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function delete($id) {
        $sql = "UPDATE subjects SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getStudents($subjectId, $academicYearId) {
        $sql = "SELECT s.* FROM students s 
                JOIN student_subjects ss ON s.id = ss.student_id 
                WHERE ss.subject_id = ? AND ss.academic_year_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$subjectId, $academicYearId]);
    }
    
    public function getTeachers($subjectId, $academicYearId) {
        $sql = "SELECT u.* FROM users u 
                JOIN teacher_subjects ts ON u.id = ts.teacher_id 
                WHERE ts.subject_id = ? AND ts.academic_year_id = ? AND u.status = 'active'
                ORDER BY u.name";
        return $this->db->fetchAll($sql, [$subjectId, $academicYearId]);
    }
}
?>
