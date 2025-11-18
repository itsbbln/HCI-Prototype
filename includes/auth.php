<?php
require_once __DIR__ . '/../config/json_db.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new JsonDB();
        $this->db->initializeDemoData();
    }
    
    public function login($email, $password) {
        $user = $this->db->getByField('users', 'email', $email);
        
        if ($user && $user['password'] === $password) {
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }
    
    public function register($name, $email, $password, $role) {
        // Check if email exists
        $existingUser = $this->db->getByField('users', 'email', $email);
        if ($existingUser) {
            return "Email already exists";
        }
        
        // Insert new user
        $newUser = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];
        
        if ($this->db->insert('users', $newUser)) {
            return true;
        }
        return "Registration failed";
    }
    
    public function logout() {
        session_destroy();
        header("Location: ../login.php");
        exit();
    }
    
    // Get user-specific data
    public function getUserData($userId, $role) {
        switch ($role) {
            case 'teacher':
                return [
                    'classes' => array_filter($this->db->getAll('classes'), function($class) use ($userId) {
                        return $class['teacher_id'] == $userId;
                    }),
                    'students' => $this->db->getAll('students')
                ];
                
            case 'student':
                $student = $this->db->getByField('students', 'email', $_SESSION['user']['email']);
                return [
                    'student' => $student,
                    'attendance' => array_filter($this->db->getAll('attendance'), function($record) use ($student) {
                        return in_array($student['id'], $record['present_students']);
                    }),
                    'sanctions' => array_filter($this->db->getAll('sanctions'), function($sanction) use ($student) {
                        return $sanction['student_id'] == $student['id'];
                    })
                ];
                
            case 'beadle':
                return [
                    'classes' => $this->db->getAll('classes'),
                    'attendance' => array_filter($this->db->getAll('attendance'), function($record) use ($userId) {
                        return $record['created_by'] == $userId;
                    })
                ];
                
            case 'prefect':
                return [
                    'students' => $this->db->getAll('students'),
                    'sanctions' => $this->db->getAll('sanctions')
                ];
                
            default:
                return [];
        }
    }
}
?>