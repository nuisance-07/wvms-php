<?php
$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

if (isset($_GET['mark_all'])) { markAllNotificationsRead($_SESSION['user_id']); redirect('/customer/notifications.php'); }
if (isset($_GET['mark'])) { markNotificationRead((int)$_GET['mark'], $_SESSION['user_id']); redirect('/customer/notifications.php'); }

$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
$stmt->execute([$_SESSION['user_id']]); $notifs = $stmt->fetchAll();
?>
<div class="card">
    <div class="card-header"><h3>🔔 Notifications</h3>
    <?php if($unreadCount>0): ?><a href="?mark_all=1" class="btn btn-sm btn-outline">Mark All Read</a><?php endif; ?>
    </div>
    <?php if(empty($notifs)): ?>
    <div class="empty-state"><div class="icon">🔔</div><h3>No Notifications</h3><p>You're all caught up!</p></div>
    <?php else: foreach($notifs as $n): ?>
    <div class="notif-page-item <?php echo $n['is_read']?'':'unread'; ?>">
        <div class="notif-icon-wrap"><?php echo match($n['type']){'order'=>'📦','payment'=>'💳','delivery'=>'🚚',default=>'🔔'}; ?></div>
        <div class="notif-content">
            <p><?php echo sanitize($n['message']); ?></p>
            <div class="notif-meta"><?php echo timeAgo($n['created_at']); ?>
            <?php if(!$n['is_read']): ?> · <a href="?mark=<?php echo $n['id']; ?>">Mark read</a><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
