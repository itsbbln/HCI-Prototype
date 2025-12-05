<?php
require_once '../../includes/header.php';
checkRole(['student']);
$user = $_SESSION['user'];

$db = new JsonDB();
$studentData = $auth->getUserData($user['id'], $user['role']);
$student = $studentData['student'] ?? null;
$attendanceRecords = $studentData['attendance'] ?? [];

// Calculate monthly stats
$currentMonth = date('n');
$currentYear = date('Y');

$monthlyAttendance = array_filter($attendanceRecords, function($record) use ($currentMonth, $currentYear) {
    $recordMonth = date('n', strtotime($record['date']));
    $recordYear = date('Y', strtotime($record['date']));
    return $recordMonth == $currentMonth && $recordYear == $currentYear;
});

$presentDays = count($monthlyAttendance);
$totalDays = count(array_filter($db->getAll('attendance'), function($record) use ($currentMonth, $currentYear) {
    $recordMonth = date('n', strtotime($record['date']));
    $recordYear = date('Y', strtotime($record['date']));
    return $recordMonth == $currentMonth && $recordYear == $currentYear;
}));
$absentDays = $totalDays - $presentDays;
$attendanceRate = $totalDays > 0 ? round(($presentDays / $totalDays) * 100) : 0;
?>
            <div class="top-bar page-header">
                <h1>My Attendance Record</h1>
                <p>View your attendance history and statistics.</p>
            </div>

            <div class="summary-cards">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Overall Attendance</span>
                        <span class="card-icon">📊</span>
                    </div>
                    <div class="card-value"><?php echo $attendanceRate; ?>%</div>
                    <div class="card-footer"><?php echo $presentDays; ?> out of <?php echo $totalDays; ?> days</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Present Days</span>
                        <span class="card-icon">🟢</span>
                    </div>
                    <div class="card-value"><?php echo $presentDays; ?></div>
                    <div class="card-footer">This month</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Absent Days</span>
                        <span class="card-icon">🔴</span>
                    </div>
                    <div class="card-value"><?php echo $absentDays; ?></div>
                    <div class="card-footer">This month</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <span class="card-title">Late Arrivals</span>
                        <span class="card-icon">🟡</span>
                    </div>
                    <div class="card-value">0</div>
                    <div class="card-footer">This month</div>
                </div>
            </div>

            <div class="attendance-calendar">
                <div class="calendar-header">
                    <h3><?php echo date('F Y'); ?></h3>
                    <div class="month-navigation">
                        <button class="btn-view" onclick="previousMonth()">← Previous</button>
                        <button class="btn-view" onclick="nextMonth()">Next →</button>
                    </div>
                </div>
                <div class="calendar-grid" id="calendarGrid">
                    <!-- Calendar will be populated by JavaScript -->
                </div>
            </div>

            <div class="recent-activity">
                <h2 class="section-title">Recent Attendance</h2>
                <ul class="activity-list" id="recentAttendance">
                    <?php 
                    $recentRecords = array_slice(array_reverse($attendanceRecords), 0, 5);
                    foreach ($recentRecords as $record): 
                        $class = $db->getById('classes', $record['class_id']);
                        $wasPresent = in_array($student['id'], $record['present_students']);
                    ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4><?php echo $wasPresent ? 'Present' : 'Absent'; ?> - <?php echo $class['name']; ?></h4>
                            <p><?php echo $record['date']; ?> • <?php echo $wasPresent ? 'Attended class' : 'Missed class'; ?></p>
                        </div>
                        <span class="activity-time">
                            <span class="attendance-badge <?php echo $wasPresent ? 'present' : 'absent'; ?>">
                                <?php echo $wasPresent ? 'Present' : 'Absent'; ?>
                            </span>
                        </span>
                    </li>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recentRecords)): ?>
                    <li class="activity-item">
                        <div class="activity-info">
                            <h4>No attendance records</h4>
                            <p>Your attendance records will appear here</p>
                        </div>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

    <script src="../../js/script.js"></script>
    <script>
        let currentMonth = <?php echo $currentMonth - 1; ?>; // JavaScript months are 0-indexed
        let currentYear = <?php echo $currentYear; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            generateCalendar();
        });

        function generateCalendar() {
            const calendarGrid = document.getElementById('calendarGrid');
            calendarGrid.innerHTML = '';
            
            // Day headers
            const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            days.forEach(day => {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day day-header';
                dayElement.textContent = day;
                calendarGrid.appendChild(dayElement);
            });
            
            // Get first day of month and number of days
            const firstDay = new Date(currentYear, currentMonth, 1).getDay();
            const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
            const today = new Date();
            
            // Add empty cells for days before first day of month
            for (let i = 0; i < firstDay; i++) {
                const emptyDay = document.createElement('div');
                emptyDay.className = 'calendar-day';
                calendarGrid.appendChild(emptyDay);
            }
            
            // Add days of the month
            for (let day = 1; day <= daysInMonth; day++) {
                const dayElement = document.createElement('div');
                dayElement.className = 'calendar-day';
                dayElement.textContent = day;
                
                // Check if this is today
                if (day === today.getDate() && currentMonth === today.getMonth() && currentYear === today.getFullYear()) {
                    dayElement.classList.add('today');
                }
                
                // Simple mock attendance - in real app, check actual attendance data
                const attendanceData = <?php echo json_encode($monthlyAttendance); ?>;
                const hasAttendance = attendanceData.some(record => {
                    const recordDate = new Date(record.date);
                    return recordDate.getDate() === day && 
                           recordDate.getMonth() === currentMonth && 
                           recordDate.getFullYear() === currentYear;
                });
                
                if (hasAttendance) {
                    dayElement.classList.add('present-day');
                } else if (day <= today.getDate() && currentMonth <= today.getMonth() && currentYear <= today.getFullYear()) {
                    dayElement.classList.add('absent-day');
                } else {
                    dayElement.classList.add('future-day');
                }
                
                calendarGrid.appendChild(dayElement);
            }
        }

        function previousMonth() {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            generateCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            generateCalendar();
        }
    </script>

    <style>
        .attendance-calendar {
            background: white;
            margin: 0 40px 30px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 24px;
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        
        .calendar-day {
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .day-header {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }
        
        .today {
            background: #2c55f0;
            color: white;
            font-weight: 600;
        }
        
        .present-day {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }
        
        .absent-day {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        
        .future-day {
            background: #f8fafc;
            color: #9ca3af;
        }
        
        .attendance-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .present {
            background: #d1fae5;
            color: #065f46;
        }
        
        .absent {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
<?php require_once '../../includes/footer.php'; ?>