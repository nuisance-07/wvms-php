<?php
$pageTitle = 'System Reports'; $loadCharts = true;
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB(); $period=$_GET['period']??'daily';

$totalRev=$db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed'")->fetchColumn();
$totalOrd=$db->query("SELECT COUNT(*) FROM water_orders")->fetchColumn();
$deliveredOrd=$db->query("SELECT COUNT(*) FROM water_orders WHERE status='delivered'")->fetchColumn();
?>
<div class="stats-grid fade-in">
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-success">💰</div>
        </div>
        <div class="stat-value"><?php echo formatCurrency($totalRev); ?></div>
        <div class="stat-label">Total Revenue</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-primary">📦</div>
        </div>
        <div class="stat-value"><?php echo $totalOrd; ?></div>
        <div class="stat-label">Total Orders</div>
    </div>
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon-wrap stat-icon-success">✅</div>
        </div>
        <div class="stat-value"><?php echo $deliveredOrd; ?></div>
        <div class="stat-label">Delivered Orders</div>
    </div>
</div>

<div class="fade-in mb-8">
    <div class="tabs" style="background:var(--surface); display:inline-flex; border-radius:8px; padding:4px; border:1px solid var(--border)">
        <a href="?period=daily" class="tab <?php echo $period==='daily'?'active':''; ?>" style="border:none">Daily</a>
        <a href="?period=weekly" class="tab <?php echo $period==='weekly'?'active':''; ?>" style="border:none">Weekly</a>
        <a href="?period=monthly" class="tab <?php echo $period==='monthly'?'active':''; ?>" style="border:none">Monthly</a>
    </div>
</div>

<div class="grid-2 fade-in mb-8">
    <div class="card" style="margin-bottom:0">
        <h4 class="mb-4 text-primary">Revenue</h4>
        <div style="position:relative; height:300px; width:100%">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>
    <div class="card" style="margin-bottom:0">
        <h4 class="mb-4 text-primary">Orders</h4>
        <div style="position:relative; height:300px; width:100%">
            <canvas id="ordersChart"></canvas>
        </div>
    </div>
</div>

<?php
$topVendors=$db->query("SELECT v.business_name, COUNT(wo.id) as orders, COALESCE(SUM(p.amount),0) as rev FROM vendors v LEFT JOIN water_orders wo ON wo.vendor_id=v.id AND wo.status='delivered' LEFT JOIN payments p ON p.order_id=wo.id AND p.status='confirmed' GROUP BY v.id ORDER BY rev DESC LIMIT 10")->fetchAll();
$topAreas=$db->query("SELECT delivery_address,COUNT(*) as cnt FROM water_orders WHERE status='delivered' GROUP BY delivery_address ORDER BY cnt DESC LIMIT 10")->fetchAll();
?>
<div class="grid-2 fade-in" style="gap:24px; margin-top:24px">
    <div class="table-wrapper" style="margin-bottom:0">
        <div class="table-header-row">
            <div class="table-title">🏆 Top Vendors</div>
            <button onclick="window.print()" class="btn btn-sm btn-secondary">🖨️ Print</button>
        </div>
        <table class="data-table">
            <thead><tr><th>Vendor</th><th>Orders</th><th>Revenue</th></tr></thead>
            <tbody>
            <?php foreach($topVendors as $v): ?>
                <tr><td><?php echo sanitize($v['business_name']); ?></td><td><?php echo $v['orders']; ?></td><td><?php echo formatCurrency($v['rev']); ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="table-wrapper" style="margin-bottom:0">
        <div class="table-header-row">
            <div class="table-title">📍 Busiest Areas</div>
        </div>
        <table class="data-table">
            <thead><tr><th>Area</th><th>Orders</th></tr></thead>
            <tbody>
            <?php foreach($topAreas as $a): ?>
                <tr><td><?php echo sanitize($a['delivery_address']); ?></td><td><?php echo $a['cnt']; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    loadChartData('<?php echo $period; ?>').then(data => {
        if(data.revenue) createBarChart('revenueChart', data.revenue.labels, data.revenue.data);
        if(data.orders) createLineChart('ordersChart', data.orders.labels, data.orders.data);
    });
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
