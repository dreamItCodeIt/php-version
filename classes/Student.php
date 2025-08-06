<?php
class Student {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT s.*, ay.year as academic_year FROM students s 
                JOIN academic_years ay ON s.academic_year_id = ay.id 
                WHERE s.id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function getAll($status = 'active') {
        $sql = "SELECT s.*, ay.year as academic_year FROM students s 
                JOIN academic_years ay ON s.academic_year_id = ay.id 
                WHERE s.status = ? ORDER BY s.name";
        return $this->db->fetchAll($sql, [$status]);
    }
    
    public function getByClass($classId, $academicYearId) {
        $sql = "SELECT s.*, ay.year as academic_year FROM students s 
                JOIN academic_years ay ON s.academic_year_id = ay.id 
                JOIN student_classes sc ON s.id = sc.student_id 
                WHERE sc.class_id = ? AND sc.academic_year_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$classId, $academicYearId]);
    }
    
    public function getBySubject($subjectId, $academicYearId) {
        $sql = "SELECT s.*, ay.year as academic_year FROM students s 
                JOIN academic_years ay ON s.academic_year_id = ay.id 
                JOIN student_subjects ss ON s.id = ss.student_id 
                WHERE ss.subject_id = ? AND ss.academic_year_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$subjectId, $academicYearId]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO students (admission_no, name, gender, date_of_birth, current_form, current_term, academic_year_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['admission_no'],
                $data['name'],
                $data['gender'],
                $data['date_of_birth'],
                $data['current_form'],
                $data['current_term'] ?? 1,
                $data['academic_year_id'],
                'active'
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function update($id, $data) {
        $sql = "UPDATE students SET name = ?, gender = ?, date_of_birth = ?, current_form = ?, 
                current_term = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['gender'],
                $data['date_of_birth'],
                $data['current_form'],
                $data['current_term'],
                $id
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function delete($id) {
        $sql = "UPDATE students SET status = 'withdrawn', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getSubjects($studentId, $academicYearId) {
        $sql = "SELECT s.* FROM subjects s 
                JOIN student_subjects ss ON s.id = ss.subject_id 
                WHERE ss.student_id = ? AND ss.academic_year_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$studentId, $academicYearId]);
    }
    
    public function assignSubjects($studentId, $subjectIds, $academicYearId) {
        try {
            $this->db->beginTransaction();
            
            // Remove existing assignments
            $sql = "DELETE FROM student_subjects WHERE student_id = ? AND academic_year_id = ?";
            $this->db->query($sql, [$studentId, $academicYearId]);
            
            // Add new assignments
            foreach ($subjectIds as $subjectId) {
                $sql = "INSERT INTO student_subjects (student_id, subject_id, academic_year_id) VALUES (?, ?, ?)";
                $this->db->query($sql, [$studentId, $subjectId, $academicYearId]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    public function assignToClass($studentId, $classId, $academicYearId) {
        try {
            // Remove existing class assignment
            $sql = "DELETE FROM student_classes WHERE student_id = ? AND academic_year_id = ?";
            $this->db->query($sql, [$studentId, $academicYearId]);
            
            // Add new class assignment
            $sql = "INSERT INTO student_classes (student_id, class_id, academic_year_id) VALUES (?, ?, ?)";
            $this->db->query($sql, [$studentId, $classId, $academicYearId]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function generateAdmissionNumber() {
        $sql = "SELECT MAX(CAST(SUBSTR(admission_no, 4) AS INTEGER)) as max_num FROM students WHERE admission_no LIKE 'STD%'";
        $result = $this->db->fetchOne($sql);
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return 'STD' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
?>
