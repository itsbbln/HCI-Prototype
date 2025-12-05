<?php
class JsonDB {
    private $dataDir = __DIR__ . '/../data/';
    
    public function __construct() {
        // Create data directory if it doesn't exist
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }
    }
    
    private function getFilePath($table) {
        return $this->dataDir . $table . '.json';
    }
    
    public function getAll($table) {
        $file = $this->getFilePath($table);
        if (!file_exists($file)) {
            return [];
        }
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    
    public function getById($table, $id) {
        $items = $this->getAll($table);
        foreach ($items as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }
        return null;
    }
    
    public function getByField($table, $field, $value) {
        $items = $this->getAll($table);
        foreach ($items as $item) {
            if (isset($item[$field]) && $item[$field] == $value) {
                return $item;
            }
        }
        return null;
    }
    
    public function insert($table, $data) {
        $items = $this->getAll($table);
        $data['id'] = count($items) + 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $items[] = $data;
        return $this->saveAll($table, $items);
    }
    
    public function update($table, $id, $data) {
        $items = $this->getAll($table);
        foreach ($items as &$item) {
            if ($item['id'] == $id) {
                $item = array_merge($item, $data);
                $item['updated_at'] = date('Y-m-d H:i:s');
                return $this->saveAll($table, $items);
            }
        }
        return false;
    }
    
    public function delete($table, $id) {
        $items = $this->getAll($table);
        $newItems = array_filter($items, function($item) use ($id) {
            return $item['id'] != $id;
        });
        return $this->saveAll($table, array_values($newItems));
    }
    
    private function saveAll($table, $items) {
        $file = $this->getFilePath($table);
        return file_put_contents($file, json_encode($items, JSON_PRETTY_PRINT));
    }
    
    // Initialize demo data if files don't exist
    public function initializeDemoData() {
        $tables = ['users', 'students', 'classes', 'attendance', 'sanctions', 'anecdotal', 'notifications'];
        
        foreach ($tables as $table) {
            if (!file_exists($this->getFilePath($table))) {
                $demoData = $this->getDemoData($table);
                $this->saveAll($table, $demoData);
            }
        }
    }
    
    private function getDemoData($table) {
        switch ($table) {
            case 'users':
                return [
                    [
                        'id' => 1,
                        'name' => 'Teacher Name',
                        'email' => 'teacher@school.edu',
                        'password' => 'teacher123',
                        'role' => 'teacher',
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'name' => 'Prefect Name',
                        'email' => 'prefect@school.edu',
                        'password' => 'prefect123',
                        'role' => 'prefect',
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 3,
                        'name' => 'Juan Dela Cruz',
                        'email' => 'student@school.edu',
                        'password' => 'student123',
                        'role' => 'student',
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    // Beadle demo user removed
                ];
                
            case 'students':
                return [
                    [
                        'id' => 1,
                        'student_id' => '2024001',
                        'name' => 'Juan Dela Cruz',
                        'email' => 'student@school.edu',
                        'grade' => '10-A',
                        'offenses' => 2,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'student_id' => '2024002',
                        'name' => 'Maria Santos',
                        'email' => 'maria@school.edu',
                        'grade' => '10-A',
                        'offenses' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 3,
                        'student_id' => '2024003',
                        'name' => 'Pedro Reyes',
                        'email' => 'pedro@school.edu',
                        'grade' => '10-B',
                        'offenses' => 1,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 4,
                        'student_id' => '2024004',
                        'name' => 'Anna Garcia',
                        'email' => 'anna@school.edu',
                        'grade' => '10-B',
                        'offenses' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
                
            case 'classes':
                return [
                    [
                        'id' => 1,
                        'name' => 'English',
                        'section' => 'Grade 10-A',
                        'schedule' => 'Mon, Wed, Fri • 8:00 AM',
                        'room' => 'Room 201',
                        'teacher_id' => 1,
                        'students' => [1, 2],
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'name' => 'Mathematics',
                        'section' => 'Grade 10-B',
                        'schedule' => 'Tue, Thu • 9:00 AM',
                        'room' => 'Room 305',
                        'teacher_id' => 1,
                        'students' => [3, 4],
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
                
            case 'attendance':
                return [
                    [
                        'id' => 1,
                        'class_id' => 1,
                        'date' => date('Y-m-d'),
                        'created_by' => 1,
                        'present_students' => [1, 2],
                        'status' => 'approved',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
                
            case 'sanctions':
                return [
                    [
                        'id' => 1,
                        'student_id' => 1,
                        'violation_type' => 'Late Arrival',
                        'sanction_level' => 'minor',
                        'description' => 'Arrived 15 minutes late to class without valid excuse.',
                        'assigned_sanction' => 'Warning and parent notification. Next offense will result in detention.',
                        'date_issued' => date('Y-m-d'),
                        'status' => 'active',
                        'issued_by' => 2,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'student_id' => 2,
                        'violation_type' => 'Disruptive Behavior',
                        'sanction_level' => 'major',
                        'description' => 'Repeatedly disrupted class by talking loudly and distracting other students.',
                        'assigned_sanction' => '2-hour detention and behavior contract.',
                        'date_issued' => date('Y-m-d', strtotime('-1 day')),
                        'status' => 'completed',
                        'issued_by' => 2,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
                    ]
                ];
                
            case 'anecdotal':
                return [
                    [
                        'id' => 1,
                        'student_id' => 1,
                        'incident_type' => 'Late Arrival',
                        'date' => '2024-11-15',
                        'description' => 'Student arrived 15 minutes late to class without valid excuse.',
                        'reported_by' => 1,
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'student_id' => 2,
                        'incident_type' => 'Disruptive Behavior',
                        'date' => '2024-11-14',
                        'description' => 'Student was talking loudly and disrupting the class multiple times.',
                        'reported_by' => 1,
                        'status' => 'approved',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ];
            case 'notifications':
                return [
                    [
                        'id' => 1,
                        'user_id' => 3, // demo student user
                        'title' => 'Welcome to the portal',
                        'message' => 'This is a sample notification. Real alerts will appear here when your records are updated.',
                        'type' => 'system',
                        'is_read' => false,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
                    ],
                    [
                        'id' => 2,
                        'user_id' => 3, // same student
                        'title' => 'Sanction status update',
                        'message' => 'Your recent sanction has been marked as completed.',
                        'type' => 'sanction',
                        'is_read' => true,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours'))
                    ],
                    [
                        'id' => 3,
                        'user_id' => 1, // teacher
                        'title' => 'Attendance reminder',
                        'message' => 'Remember to finalize todays attendance before the end of the day.',
                        'type' => 'attendance',
                        'is_read' => false,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours'))
                    ],
                    [
                        'id' => 4,
                        'user_id' => 2, // prefect
                        'title' => 'Pending sanctions to review',
                        'message' => 'There are sanctions that still need your review in the system.',
                        'type' => 'sanction',
                        'is_read' => false,
                        'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours'))
                    ]
                ];
    
                
            default:
                return [];

        }
    }
}
?>