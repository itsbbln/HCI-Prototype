<?php
require_once '../../includes/header.php';

// Allow all main roles to access their own notifications
checkRole(['teacher']);
$user = $_SESSION['user'];

$db = new JsonDB();
$teacherData = $auth->getUserData($user['id'], $user['role']);
$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mark single notification as read
    if (isset($_POST['mark_read']) && isset($_POST['notification_id'])) {
        $notificationId = (int) $_POST['notification_id'];
        $notification = $db->getById('notifications', $notificationId);

        if ($notification && $notification['user_id'] == $user['id']) {
            if ($db->update('notifications', $notificationId, ['is_read' => true])) {
                $success = 'Notification marked as read.';
            } else {
                $error = 'Unable to update notification. Please try again.';
            }
        } else {
            $error = 'Notification not found or not owned by this user.';
        }
    }

    // Mark all notifications as read
    if (isset($_POST['mark_all_read'])) {
        $all = $db->getAll('notifications');
        foreach ($all as $n) {
            if ($n['user_id'] == $user['id'] && empty($n['is_read'])) {
                $db->update('notifications', $n['id'], ['is_read' => true]);
            }
        }
        $success = 'All notifications marked as read.';
    }
}

// Load notifications for this user
$notifications = array_filter($db->getAll('notifications'), function($n) use ($user) {
    return $n['user_id'] == $user['id'];
});

// Sort newest first
usort($notifications, function($a, $b) {
    $aTime = isset($a['created_at']) ? strtotime($a['created_at']) : 0;
    $bTime = isset($b['created_at']) ? strtotime($b['created_at']) : 0;
    return $bTime <=> $aTime;
});

$unreadCount = count(array_filter($notifications, function($n) {
    return empty($n['is_read']) || $n['is_read'] == false;
}));
?>

<div class="top-bar page-header">
    <div>
        <h1>Notifications</h1>
        <p>Alerts related to your attendance, sanctions, and system updates.</p>
    </div>

    <?php if ($unreadCount > 0): ?>
        <form method="POST">
            <input type="hidden" name="mark_all_read" value="1">
            <button type="submit" class="btn-new">
                Mark all as read (<?php echo $unreadCount; ?>)
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if ($success): ?>
    <div style="background:#d1fae5;color:#065f46;padding:12px;border-radius:8px;margin-bottom:16px;">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div style="background:#fee2e2;color:#991b1b;padding:12px;border-radius:8px;margin-bottom:16px;">
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<div class="notification-container">
    <table class="notification-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Message</th>
                <th>Type</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($notifications)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6e6e6e;">
                        No notifications yet.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($notifications as $index => $n): 
                    $isRead = !empty($n['is_read']);
                    $type = isset($n['type']) ? $n['type'] : 'system';
                    $statusClass = $isRead ? 'status-read' : 'status-unread';
                ?>
                <tr class="notification-row <?php echo $statusClass; ?>">
                    <td><?php echo $index + 1; ?></td>
                    <td><?php echo htmlspecialchars($n['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($n['message'])); ?></td>
                    <td>
                        <span class="notification-type-badge">
                            <?php echo htmlspecialchars($type); ?>
                        </span>
                    </td>
                    <td><?php echo isset($n['created_at']) ? date('M d, Y', strtotime($n['created_at'])) : ''; ?></td>
                    <td>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo $isRead ? 'Read' : 'Unread'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if (!$isRead): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                                <input type="hidden" name="mark_read" value="1">
                                <button type="submit" class="btn-view">
                                    Mark as read
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:#9ca3af;font-size:0.9rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
