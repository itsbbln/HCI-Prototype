<?php
require_once '../../includes/header.php';
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$teacherData = $auth->getUserData($user['id'], $user['role']);
$teacherClasses = $teacherData['classes'];
?>
            <div class="top-bar page-header">
                <h1>My Classes</h1>
                <p>View and manage your assigned classes.</p>
            </div>

            <div class="classes-container">
                <table class="classes-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Schedule</th>
                            <th>Room</th>
                            <th>Students</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($teacherClasses)): ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #6e6e6e;">
                                    No classes assigned yet. Please contact administration.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teacherClasses as $index => $class): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><span class="class-name"><?php echo $class['name']; ?></span></td>
                                <td><?php echo $class['section']; ?></td>
                                <td><?php echo $class['schedule']; ?></td>
                                <td><?php echo $class['room']; ?></td>
                                <td><?php echo count($class['students']); ?></td>
                                <td>
                                    <a href="class-details.php?id=<?php echo $class['id']; ?>" class="btn-view">View</a>
                                    <a href="attendance.php?class_id=<?php echo $class['id']; ?>" class="btn-view" style="background: #10b981; margin-left: 5px;">Attendance</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
<?php require_once '../../includes/footer.php'; ?>