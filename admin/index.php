<?php
$pageTitle = 'Admin Dashboard'; $loadCharts = true;
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB(); $today = date('Y-m-d');

$totalUsers = $db->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$totalVendors = $db->query("SELECT COUNT(*) FROM vendors")->fetchColumn();
$todayOrders = $db->prepare("SELECT COUNT(*) FROM water_orders WHERE DATE(created_at)=?"); $todayOrders->execute([$today]);
$totalRevenue = $db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed'")->fetchColumn();
$pendingOrders = $db->query("SELECT COUNT(*) FROM water_orders WHERE status='pending'")->fetchColumn();
?>
<div class="stats-grid">
    <div class="stat-card"><div class="stat-icon">👥</div><div class="stat-info"><h3><?php echo $totalUsers; ?></h3><p>Total Customers</p></div></div>
    <div class="stat-card"><div class="stat-icon">🏪</div><div class="stat-info"><h3><?php echo $totalVendors; ?></h3><p>Total Vendors</p></div></div>
    <div class="stat-card orange"><div class="stat-icon">📦</div><div class="stat-info"><h3><?php echo $todayOrders->fetchColumn(); ?></h3><p>Orders Today</p></div></div>
    <div class="stat-card green"><div class="stat-icon">💰</div><div class="stat-info"><h3><?php echo formatCurrency($totalRevenue); ?></h3><p>Total Revenue</p></div></div>
</div>

<div class="quick-actions">
    <a href="/admin/users.php" class="btn btn-primary">👥 Manage Users</a>
    <a href="/admin/vendors.php" class="btn btn-outline">🏪 Vendor Performance</a>
    <a href="/admin/orders.php" class="btn btn-outline">📦 All Orders</a>
    <a href="/admin/reports.php" class="btn btn-outline">📈 Reports</a>
</div>

<div class="charts-grid">
    <div class="chart-container"><h4 style="margin-bottom:16px">Revenue Trend (Last 7 Days)</h4><canvas id="revenueChart" height="300"></canvas></div>
    <div class="chart-container"><h4 style="margin-bottom:16px">Orders Trend (Last 7 Days)</h4><canvas id="ordersChart" height="300"></canvas></div>
</div>

<?php
$recent = $db->query("SELECT wo.*, u.name as customer_name, v.business_name FROM water_orders wo JOIN users u ON wo.customer_id=u.id JOIN vendors v ON wo.vendor_id=v.id ORDER BY wo.created_at DESC LIMIT 10");
$recentOrders = $recent->fetchAll();
?>
<div class="table-container" style="margin-top:20px">
    <div class="table-header"><h3>Recent Orders</h3></div>
    <table class="data-table"><thead><tr><th>#</th><th>Customer</th><th>Vendor</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php foreach($recentOrders as $o): ?>
    <tr><td>#<?php echo $o['id']; ?></td><td><?php echo sanitize($o['customer_name']); ?></td><td><?php echo sanitize($o['business_name']); ?></td><td><?php echo $o['quantity_litres']; ?>L</td><td><?php echo formatCurrency($o['total_amount']); ?></td><td><?php echo getStatusBadge($o['status']); ?></td><td><?php echo formatDate($o['created_at']); ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
</div>

<script>
loadChartData('daily').then(data => {
    if(data.revenue) createBarChart('revenueChart', data.revenue.labels, data.revenue.data);
    if(data.orders) createLineChart('ordersChart', data.orders.labels, data.orders.data);
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
