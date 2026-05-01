<?php
$pageTitle = 'Announcements';
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCSRF();
    $message=sanitize($_POST['message']??''); $target=$_POST['target']??'all';
    if($message) {
        $where="WHERE status='active'";
        if($target==='customers') $where.=" AND role='customer'";
        elseif($target==='vendors') $where.=" AND role='vendor'";
        $users=$db->query("SELECT id FROM users $where")->fetchAll();
        $stmt=$db->prepare("INSERT INTO notifications (user_id,message,type) VALUES (?,'$message','system')");
        foreach($users as $u) $stmt->execute([$u['id']]);
        setFlash('success','Announcement sent to '.count($users).' users.');
    }
    redirect('/admin/notifications.php');
}

$recent=$db->query("SELECT n.*,u.name FROM notifications n JOIN users u ON n.user_id=u.id WHERE n.type='system' ORDER BY n.created_at DESC LIMIT 20")->fetchAll();
?>
<div class="card">
    <div class="card-header"><h3>📢 Send Announcement</h3></div>
    <form method="POST"><?php csrfField(); ?>
    <div class="form-group"><label>Target Audience</label>
        <select name="target" class="form-control"><option value="all">All Users</option><option value="customers">Customers Only</option><option value="vendors">Vendors Only</option></select>
    </div>
    <div class="form-group"><label>Message</label><textarea name="message" class="form-control" rows="3" placeholder="Type your announcement..." required></textarea></div>
    <button class="btn btn-primary">📤 Send Announcement</button>
    </form>
</div>

<div class="table-container">
    <div class="table-header"><h3>Recent Announcements</h3></div>
    <table class="data-table"><thead><tr><th>Message</th><th>Sent To</th><th>Date</th></tr></thead><tbody>
    <?php if(empty($recent)): ?><tr><td colspan="3" class="no-data">No announcements yet.</td></tr>
    <?php else: foreach($recent as $n): ?>
    <tr><td><?php echo sanitize($n['message']); ?></td><td><?php echo sanitize($n['name']); ?></td><td><?php echo timeAgo($n['created_at']); ?></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
