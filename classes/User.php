<?php
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function findByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]);
    }
    
    public function getAll($role = null) {
        $sql = "SELECT * FROM users WHERE status = 'active'";
        $params = [];
        
        if ($role) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }
        
        $sql .= " ORDER BY name";
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getTeachers() {
        $sql = "SELECT * FROM users WHERE role IN ('teacher', 'class_teacher') AND status = 'active' ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    public function create($data) {
        $sql = "INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)";
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['email'],
                $hashedPassword,
                $data['role'],
                $data['phone'] ?? null,
                'active'
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function update($id, $data) {
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, phone = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['email'],
                $data['role'],
                $data['phone'] ?? null,
                $id
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function delete($id) {
        $sql = "UPDATE users SET status = 'inactive', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getTeacherSubjects($teacherId, $academicYearId) {
        $sql = "SELECT s.* FROM subjects s 
                JOIN teacher_subjects ts ON s.id = ts.subject_id 
                WHERE ts.teacher_id = ? AND ts.academic_year_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$teacherId, $academicYearId]);
    }
    
    public function assignSubjects($teacherId, $subjectIds, $academicYearId) {
        try {
            $this->db->beginTransaction();
            
            // Remove existing assignments
            $sql = "DELETE FROM teacher_subjects WHERE teacher_id = ? AND academic_year_id = ?";
            $this->db->query($sql, [$teacherId, $academicYearId]);
            
            // Add new assignments
            foreach ($subjectIds as $subjectId) {
                $sql = "INSERT INTO teacher_subjects (teacher_id, subject_id, academic_year_id) VALUES (?, ?, ?)";
                $this->db->query($sql, [$teacherId, $subjectId, $academicYearId]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
}
?>
