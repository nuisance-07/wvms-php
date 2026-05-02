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
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-primary">📦</div>
            <div class="stat-trend trend-up">↑ Total</div>
        </div>
        <div class="stat-value"><?php echo $totalOrders->fetchColumn(); ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-success">✅</div>
            <div class="stat-trend trend-up">↑ Lifetime</div>
        </div>
        <div class="stat-value"><?php echo $deliveredCount->fetchColumn(); ?></div>
        <div class="stat-label">Delivered Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-warning">💰</div>
        </div>
        <div class="stat-value"><?php echo formatCurrency($totalSpent->fetchColumn()); ?></div>
        <div class="stat-label">Total Spent</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-primary">🔔</div>
        </div>
        <div class="stat-value"><?php echo $unreadCount; ?></div>
        <div class="stat-label">Unread Alerts</div>
    </div>
</div>

<?php if ($active): ?>
<div class="card fade-in">
    <div class="flex justify-between items-center mb-4">
        <h3>🚚 Active Order</h3>
        <?php echo getStatusBadge($active['status']); ?>
    </div>
    <div class="grid-2 mb-6">
        <div><span class="label">Order #</span><br><strong><?php echo $active['id']; ?></strong></div>
        <div><span class="label">Vendor</span><br><strong><?php echo sanitize($active['business_name']); ?></strong></div>
        <div><span class="label">Quantity</span><br><strong><?php echo $active['quantity_litres']; ?>L</strong></div>
        <div><span class="label">Total Amount</span><br><strong><?php echo formatCurrency($active['total_amount']); ?></strong></div>
    </div>
    <a href="/customer/track_order.php?id=<?php echo $active['id']; ?>" class="btn btn-primary">Track Order →</a>
</div>
<?php else: ?>
<div class="card text-center fade-in" style="padding:48px 24px">
    <div style="font-size:3rem;margin-bottom:16px">💧</div>
    <h3 class="mb-4">No Active Orders</h3>
    <p style="color:var(--text-secondary);margin-bottom:24px">Your water supply looks stable. Ready to order fresh water?</p>
    <a href="/customer/place_order.php" class="btn btn-primary">Place New Order</a>
</div>
<?php endif; ?>

<?php
$recent = $db->prepare("SELECT wo.*, v.business_name FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id WHERE wo.customer_id=? ORDER BY wo.created_at DESC LIMIT 5");
$recent->execute([$uid]); $recentOrders = $recent->fetchAll();
if ($recentOrders): ?>
<div class="table-wrapper fade-in">
    <div class="table-header-row">
        <div class="table-title">Recent Orders</div>
        <a href="/customer/orders.php" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>Order #</th><th>Vendor</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
        <?php foreach($recentOrders as $o): ?>
            <tr>
                <td><strong>#<?php echo $o['id']; ?></strong></td>
                <td><?php echo sanitize($o['business_name']); ?></td>
                <td><?php echo $o['quantity_litres']; ?>L</td>
                <td><?php echo formatCurrency($o['total_amount']); ?></td>
                <td><?php echo getStatusBadge($o['status']); ?></td>
                <td><?php echo formatDate($o['created_at']); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
