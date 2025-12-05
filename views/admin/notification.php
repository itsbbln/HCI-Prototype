<?php
require_once '../../includes/header.php';
checkRole(['admin']);
$user = $_SESSION['user'];
$db = new JsonDB();

// Get all notifications (admin sees system-wide + personal)
$allNotifications = $db->getAll('notifications');
$personalNotifications = array_filter($allNotifications, fn($n) => $n['user_id'] == $user['id']);
$systemNotifications = array_filter($allNotifications, fn($n) => $n['type'] === 'system');

// Combine and sort
$notifications = array_merge($personalNotifications, $systemNotifications);
usort($notifications, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

$unread = count(array_filter($notifications, fn($n) => empty($n['is_read'])));
?>

<div class="top-bar page-header">
    <h1>Notifications <?php if ($unread > 0) echo "<span style='color:#ef4444;'>($unread unread)</span>"; ?></h1>
</div>

<div class="records-container">
    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <ul class="notification-list">
            <?php foreach ($notifications as $n): 
                $isRead = !empty($n['is_read']);
                $type = $n['type'] ?? 'info';
            ?>
                <li class="notification-item" style="<?php echo $isRead ? '' : 'background:#f0f4ff; border-left:4px solid #2c55f0;'; ?>">
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <strong><?php echo htmlspecialchars($n['title']); ?></strong>
                            <span style="font-size:0.75rem; padding:2px 8px; border-radius:999px; background:#e5e7eb;">
                                <?php echo ucfirst($type); ?>
                            </span>
                            <?php if (!$isRead): ?>
                                <span style="color:#10b981; font-weight:600;">New</span>
                            <?php endif; ?>
                        </div>
                        <p style="margin:6px 0; color:#4b5563;"><?php echo nl2br(htmlspecialchars($n['message'])); ?></p>
                        <small style="color:#9ca3af;">
                            <?php echo date('M d, Y • h:i A', strtotime($n['created_at'])); ?>
                        </small>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>