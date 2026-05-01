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
<div class="card-grid">
<?php foreach($vendors as $v):
    $rating = getVendorRating($v['id']);
?>
<div class="order-card">
    <div class="order-card-header"><span class="order-card-id"><?php echo sanitize($v['business_name']); ?></span><?php echo getStatusBadge($v['user_status']); ?></div>
    <div class="order-card-body">
        <div><span class="label">Owner</span><strong><?php echo sanitize($v['name']); ?></strong></div>
        <div><span class="label">Area</span><strong><?php echo sanitize($v['service_area']); ?></strong></div>
        <div><span class="label">Orders Done</span><strong><?php echo $v['completed']; ?></strong></div>
        <div><span class="label">Revenue</span><strong><?php echo formatCurrency($v['revenue']); ?></strong></div>
        <div><span class="label">Rating</span><strong><?php echo renderStars($rating['avg_rating']); ?> (<?php echo $rating['total_reviews']; ?>)</strong></div>
        <div><span class="label">Phone</span><strong><?php echo sanitize($v['phone']); ?></strong></div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
