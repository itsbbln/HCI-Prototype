<?php
require_once '../../includes/header.php';

// Allow all main roles to access their own notifications
checkRole(['student']);
$user = $_SESSION['user'];

$db = new JsonDB();
$studentData = $auth->getUserData($user['id'], $user['role']);
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

<div class="page-header">
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

<div class="card">
    <div class="card-header">
        <h2>Your Notifications</h2>
        <p style="margin:0;font-size:0.9rem;color:#6b7280;">
            Showing <?php echo count($notifications); ?> notification(s).
        </p>
    </div>

    <?php if (empty($notifications)): ?>
        <div style="padding:20px;">
            <p style="color:#6b7280;">No notifications yet.</p>
        </div>
    <?php else: ?>
        <ul class="notification-list" style="list-style:none;margin:0;padding:0;">
            <?php foreach ($notifications as $n): 
                $isRead = !empty($n['is_read']);
                $type = isset($n['type']) ? $n['type'] : 'system';
            ?>
                <li class="notification-card" style="border-bottom:1px solid #e5e7eb;padding:14px 16px;display:flex;justify-content:space-between;gap:16px;align-items:flex-start;<?php echo $isRead ? 'background:#ffffff;' : 'background:#f9fafb;'; ?>">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                            <span style="font-size:0.75rem;padding:2px 8px;border-radius:999px;
                                         background:#e5e7eb;color:#374151;text-transform:capitalize;">
                                <?php echo htmlspecialchars($type); ?>
                            </span>
                            <?php if (!$isRead): ?>
                                <span style="font-size:0.7rem;color:#10b981;">● Unread</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin:6px 0 4px 0;color:#4b5563;font-size:0.9rem;">
                            <?php echo nl2br(htmlspecialchars($n['message'])); ?>
                        </p>
                        <small style="color:#9ca3af;">
                            <?php echo isset($n['created_at']) ? date('M d, Y h:i A', strtotime($n['created_at'])) : ''; ?>
                        </small>
                    </div>

                    <?php if (!$isRead): ?>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="notification_id" value="<?php echo $n['id']; ?>">
                            <input type="hidden" name="mark_read" value="1">
                            <button type="submit" class="btn-view" style="white-space:nowrap;">
                                Mark as read
                            </button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>
