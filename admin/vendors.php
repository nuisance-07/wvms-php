<?php
$pageTitle = 'Vendor Performance';
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB();

$vendors = $db->query("SELECT v.*, u.name, u.email, u.phone, u.status as user_status,
    (SELECT COUNT(*) FROM water_orders wo WHERE wo.vendor_id=v.id AND wo.status='delivered') as completed,
    (SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN water_orders wo ON p.order_id=wo.id WHERE wo.vendor_id=v.id AND p.status='confirmed') as revenue
    FROM vendors v JOIN users u ON v.user_id=u.id ORDER BY revenue DESC")->fetchAll();
?>
<div class="card-grid fade-in">
<?php foreach($vendors as $v):
    $rating = getVendorRating($v['id']);
?>
<div class="card" style="margin-bottom:0">
    <div class="flex justify-between items-center mb-4">
        <span style="font-weight:700; color:var(--text-primary); font-size:1.1rem"><?php echo sanitize($v['business_name']); ?></span>
        <?php echo getStatusBadge($v['user_status']); ?>
    </div>
    <div class="grid-2" style="gap:12px; font-size:0.875rem">
        <div><span class="label">Owner</span><br><strong><?php echo sanitize($v['name']); ?></strong></div>
        <div><span class="label">Service Area</span><br><strong><?php echo sanitize($v['service_area']); ?></strong></div>
        <div><span class="label">Orders Done</span><br><strong><?php echo $v['completed']; ?></strong></div>
        <div><span class="label">Total Revenue</span><br><strong style="color:var(--success)"><?php echo formatCurrency($v['revenue']); ?></strong></div>
        <div><span class="label">Rating</span><br><strong><?php echo renderStars($rating['avg_rating']); ?> (<?php echo $rating['total_reviews']; ?>)</strong></div>
        <div><span class="label">Phone</span><br><strong><?php echo sanitize($v['phone']); ?></strong></div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
