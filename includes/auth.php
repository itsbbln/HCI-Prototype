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
        // After $user = $this->db->getByField('users', 'email', $email);
        if ($user && $user['role'] === 'student') {
            $studentRecord = $this->db->getByField('students', 'email', $user['email']);
            if (!$studentRecord) {
                // Auto-create missing student profile
                $this->db->insert('students', [
                    'student_id' => '2025' . str_pad($user['id'], 4, '0', STR_PAD_LEFT),
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'grade' => 'Not Assigned',
                    'offenses' => 0
                ]);
            }
        }
    }
    
    public function register($name, $email, $password, $role) {
        $existingUser = $this->db->getByField('users', 'email', $email);
        if ($existingUser) {
            return "Email already exists";
        }

        $newUser = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];

        $userId = $this->db->insert('users', $newUser);

        // AUTO-CREATE STUDENT RECORD IF ROLE IS STUDENT
        if ($role === 'student' && $userId) {
            $this->db->insert('students', [
                'name' => $name,
                'email' => $email,
                'student_id' => '2025' . str_pad($userId, 4, '0', STR_PAD_LEFT), // e.g., 20250006
                'grade' => 'Not Assigned',
                'offenses' => 0
            ]);
        }

        return $userId ? true : "Registration failed";
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