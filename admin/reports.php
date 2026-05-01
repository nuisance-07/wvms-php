<?php
$pageTitle = 'System Reports'; $loadCharts = true;
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB(); $period=$_GET['period']??'daily';

$totalRev=$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed'")->fetchColumn();
$totalOrd=$db->query("SELECT COUNT(*) FROM water_orders")->fetchColumn();
$deliveredOrd=$db->query("SELECT COUNT(*) FROM water_orders WHERE status='delivered'")->fetchColumn();
?>
<div class="stats-grid">
    <div class="stat-card green"><div class="stat-icon">💰</div><div class="stat-info"><h3><?php echo formatCurrency($totalRev); ?></h3><p>Total Revenue</p></div></div>
    <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-info"><h3><?php echo $totalOrd; ?></h3><p>Total Orders</p></div></div>
    <div class="stat-card"><div class="stat-icon">✅</div><div class="stat-info"><h3><?php echo $deliveredOrd; ?></h3><p>Delivered</p></div></div>
</div>

<div class="tabs">
    <a href="?period=daily" class="tab <?php echo $period==='daily'?'active':''; ?>">Daily</a>
    <a href="?period=weekly" class="tab <?php echo $period==='weekly'?'active':''; ?>">Weekly</a>
    <a href="?period=monthly" class="tab <?php echo $period==='monthly'?'active':''; ?>">Monthly</a>
</div>

<div class="charts-grid">
    <div class="chart-container"><h4 style="margin-bottom:16px">Revenue</h4><canvas id="revenueChart" height="300"></canvas></div>
    <div class="chart-container"><h4 style="margin-bottom:16px">Orders</h4><canvas id="ordersChart" height="300"></canvas></div>
</div>

<?php
$topVendors=$db->query("SELECT v.business_name, COUNT(wo.id) as orders, COALESCE(SUM(p.amount),0) as rev FROM vendors v LEFT JOIN water_orders wo ON wo.vendor_id=v.id AND wo.status='delivered' LEFT JOIN payments p ON p.order_id=wo.id AND p.status='confirmed' GROUP BY v.id ORDER BY rev DESC LIMIT 10")->fetchAll();
$topAreas=$db->query("SELECT delivery_address,COUNT(*) as cnt FROM water_orders WHERE status='delivered' GROUP BY delivery_address ORDER BY cnt DESC LIMIT 10")->fetchAll();
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px">
<div class="table-container"><div class="table-header"><h3>🏆 Top Vendors</h3><button onclick="window.print()" class="btn btn-sm btn-outline">🖨️ Print</button></div>
<table class="data-table"><thead><tr><th>Vendor</th><th>Orders</th><th>Revenue</th></tr></thead><tbody>
<?php foreach($topVendors as $v): ?><tr><td><?php echo sanitize($v['business_name']); ?></td><td><?php echo $v['orders']; ?></td><td><?php echo formatCurrency($v['rev']); ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<div class="table-container"><div class="table-header"><h3>📍 Busiest Areas</h3></div>
<table class="data-table"><thead><tr><th>Area</th><th>Orders</th></tr></thead><tbody>
<?php foreach($topAreas as $a): ?><tr><td><?php echo sanitize($a['delivery_address']); ?></td><td><?php echo $a['cnt']; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
</div>

<script>
loadChartData('<?php echo $period; ?>').then(data => {
    if(data.revenue) createBarChart('revenueChart', data.revenue.labels, data.revenue.data);
    if(data.orders) createLineChart('ordersChart', data.orders.labels, data.orders.data);
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
