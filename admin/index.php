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
<div class="stats-grid fade-in">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-primary">👥</div>
        </div>
        <div class="stat-value"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Total Customers</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-primary">🏪</div>
        </div>
        <div class="stat-value"><?php echo $totalVendors; ?></div>
        <div class="stat-label">Total Vendors</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-warning">📦</div>
        </div>
        <div class="stat-value"><?php echo $todayOrders->fetchColumn(); ?></div>
        <div class="stat-label">Orders Today</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-success">💰</div>
        </div>
        <div class="stat-value"><?php echo formatCurrency($totalRevenue); ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
</div>

<div class="flex gap-4 mb-6 flex-wrap fade-in">
    <a href="/admin/users.php" class="btn btn-primary">👥 Manage Users</a>
    <a href="/admin/vendors.php" class="btn btn-secondary">🏪 Vendor Performance</a>
    <a href="/admin/orders.php" class="btn btn-secondary">📦 All Orders</a>
    <a href="/admin/reports.php" class="btn btn-secondary">📈 Reports</a>
</div>

<div class="grid-2 fade-in mb-8">
    <div class="card" style="margin-bottom:0">
        <h4 class="mb-4 text-primary">Revenue Trend (Last 7 Days)</h4>
        <div style="position:relative; height:300px; width:100%">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="card" style="margin-bottom:0">
        <h4 class="mb-4 text-primary">Orders Trend (Last 7 Days)</h4>
        <div style="position:relative; height:300px; width:100%">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>
</div>

<?php
$recent = $db->query("SELECT wo.*, u.name as customer_name, v.business_name FROM water_orders wo JOIN users u ON wo.customer_id=u.id JOIN vendors v ON wo.vendor_id=v.id ORDER BY wo.created_at DESC LIMIT 10");
$recentOrders = $recent->fetchAll();
?>
<div class="table-wrapper fade-in">
    <div class="table-header-row">
        <div class="table-title">Recent Orders</div>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>#</th><th>Customer</th><th>Vendor</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
        <?php foreach($recentOrders as $o): ?>
            <tr>
                <td><strong>#<?php echo $o['id']; ?></strong></td>
                <td><?php echo sanitize($o['customer_name']); ?></td>
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

<script>
document.addEventListener("DOMContentLoaded", function() {
    loadChartData('daily').then(data => {
        if(data.revenue) createBarChart('revenueChart', data.revenue.labels, data.revenue.data);
        if(data.orders) createLineChart('ordersChart', data.orders.labels, data.orders.data);
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
