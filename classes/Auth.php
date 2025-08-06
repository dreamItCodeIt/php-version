<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = ? AND status = 'active'";
        $user = $this->db->fetchOne($sql, [$email]);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            
            // Log the login
            $this->logActivity($user['id'], 'login', 'users', $user['id']);
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
        }
        
        session_destroy();
        return true;
    }
    
    public function register($data) {
        $sql = "INSERT INTO users (name, email, password, role, phone, status) VALUES (?, ?, ?, ?, ?, ?)";
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        try {
            $this->db->query($sql, [
                $data['name'],
                $data['email'],
                $hashedPassword,
                $data['role'] ?? 'teacher',
                $data['phone'] ?? null,
                'active'
            ]);
            
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->db->fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);
        
        if ($user && password_verify($oldPassword, $user['password'])) {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $this->db->query($sql, [$hashedPassword, $userId]);
            
            $this->logActivity($userId, 'password_change', 'users', $userId);
            return true;
        }
        
        return false;
    }
    
    private function logActivity($userId, $action, $tableName, $recordId, $oldValues = null, $newValues = null) {
        $sql = "INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $userId,
            $action,
            $tableName,
            $recordId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}
?>
