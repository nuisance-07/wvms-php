<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();
$uid = $_SESSION['user_id'];

$activeOrder = $db->prepare("SELECT wo.*, v.business_name FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id WHERE wo.customer_id=? AND wo.status NOT IN('delivered','cancelled') ORDER BY wo.created_at DESC LIMIT 1");
$activeOrder->execute([$uid]); $active = $activeOrder->fetch();

$totalOrders = $db->prepare("SELECT COUNT(*) FROM water_orders WHERE customer_id=?"); $totalOrders->execute([$uid]);
$totalSpent = $db->prepare("SELECT COALESCE(SUM(p.amount),0) FROM payments p JOIN water_orders wo ON p.order_id=wo.id WHERE wo.customer_id=? AND p.status='confirmed'"); $totalSpent->execute([$uid]);
$deliveredCount = $db->prepare("SELECT COUNT(*) FROM water_orders WHERE customer_id=? AND status='delivered'"); $deliveredCount->execute([$uid]);
?>

<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-info"><h3><?php echo $totalOrders->fetchColumn(); ?></h3><p>Total Orders</p></div></div>
    <div class="stat-card green"><div class="stat-icon">✅</div><div class="stat-info"><h3><?php echo $deliveredCount->fetchColumn(); ?></h3><p>Delivered</p></div></div>
    <div class="stat-card orange"><div class="stat-icon">💰</div><div class="stat-info"><h3><?php echo formatCurrency($totalSpent->fetchColumn()); ?></h3><p>Total Spent</p></div></div>
    <div class="stat-card"><div class="stat-icon">🔔</div><div class="stat-info"><h3><?php echo $unreadCount; ?></h3><p>Unread Alerts</p></div></div>
</div>

<?php if ($active): ?>
<div class="card">
    <div class="card-header"><h3>🚚 Active Order</h3><?php echo getStatusBadge($active['status']); ?></div>
    <div class="order-card-body">
        <div><span class="label">Order #</span><strong><?php echo $active['id']; ?></strong></div>
        <div><span class="label">Vendor</span><strong><?php echo sanitize($active['business_name']); ?></strong></div>
        <div><span class="label">Quantity</span><strong><?php echo $active['quantity_litres']; ?>L</strong></div>
        <div><span class="label">Total</span><strong><?php echo formatCurrency($active['total_amount']); ?></strong></div>
    </div>
    <div style="margin-top:16px"><a href="/customer/track_order.php?id=<?php echo $active['id']; ?>" class="btn btn-primary btn-sm">Track Order →</a></div>
</div>
<?php else: ?>
<div class="card" style="text-align:center;padding:40px">
    <div style="font-size:3rem;margin-bottom:12px">💧</div>
    <h3>No Active Orders</h3><p style="color:var(--text-light);margin:8px 0 20px">Ready to order fresh water?</p>
    <a href="/customer/place_order.php" class="btn btn-primary btn-lg">Place New Order</a>
</div>
<?php endif; ?>

<?php
$recent = $db->prepare("SELECT wo.*, v.business_name FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id WHERE wo.customer_id=? ORDER BY wo.created_at DESC LIMIT 5");
$recent->execute([$uid]); $recentOrders = $recent->fetchAll();
if ($recentOrders): ?>
<div class="table-container">
    <div class="table-header"><h3>Recent Orders</h3><a href="/customer/orders.php" class="btn btn-outline btn-sm">View All</a></div>
    <table class="data-table"><thead><tr><th>Order #</th><th>Vendor</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php foreach($recentOrders as $o): ?>
    <tr><td><strong>#<?php echo $o['id']; ?></strong></td><td><?php echo sanitize($o['business_name']); ?></td><td><?php echo $o['quantity_litres']; ?>L</td><td><?php echo formatCurrency($o['total_amount']); ?></td><td><?php echo getStatusBadge($o['status']); ?></td><td><?php echo formatDate($o['created_at']); ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
