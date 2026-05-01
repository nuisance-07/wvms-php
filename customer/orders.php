<?php
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

$status = $_GET['status'] ?? '';
$where = "WHERE wo.customer_id=?"; $params = [$_SESSION['user_id']];
if ($status && in_array($status, ['pending','accepted','dispatched','delivered','cancelled'])) {
    $where .= " AND wo.status=?"; $params[] = $status;
}
$count = $db->prepare("SELECT COUNT(*) FROM water_orders wo $where"); $count->execute($params);
$page = paginate($count->fetchColumn(), (int)($_GET['page']??1), 10);

$stmt = $db->prepare("SELECT wo.*, v.business_name FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id $where ORDER BY wo.created_at DESC LIMIT {$page['per_page']} OFFSET {$page['offset']}");
$stmt->execute($params); $orders = $stmt->fetchAll();
?>

<div class="filters">
    <a href="/customer/orders.php" class="btn btn-sm <?php echo !$status?'btn-primary':'btn-outline'; ?>">All</a>
    <?php foreach(['pending','accepted','dispatched','delivered','cancelled'] as $s): ?>
    <a href="?status=<?php echo $s; ?>" class="btn btn-sm <?php echo $status===$s?'btn-primary':'btn-outline'; ?>"><?php echo ucfirst($s); ?></a>
    <?php endforeach; ?>
</div>

<div class="table-container">
    <div class="table-header"><h3>Order History</h3><input type="text" class="table-search" placeholder="Search orders..." onkeyup="filterTable('this','ordersTable')" id="orderSearch"></div>
    <table class="data-table" id="ordersTable"><thead><tr><th>#</th><th>Vendor</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th><th>Action</th></tr></thead><tbody>
    <?php if(empty($orders)): ?>
    <tr><td colspan="7" class="no-data">No orders found.</td></tr>
    <?php else: foreach($orders as $o): ?>
    <tr>
        <td><strong>#<?php echo $o['id']; ?></strong></td>
        <td><?php echo sanitize($o['business_name']); ?></td>
        <td><?php echo $o['quantity_litres']; ?>L</td>
        <td><?php echo formatCurrency($o['total_amount']); ?></td>
        <td><?php echo getStatusBadge($o['status']); ?></td>
        <td><?php echo formatDate($o['created_at']); ?></td>
        <td><a href="/customer/track_order.php?id=<?php echo $o['id']; ?>" class="btn btn-sm btn-outline">Track</a></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody></table>
    <?php echo renderPagination($page, '/customer/orders.php'); ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
