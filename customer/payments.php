<?php
$pageTitle = 'Payments';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

$stmt = $db->prepare("SELECT p.*, wo.quantity_litres, wo.total_amount as order_total, v.business_name FROM payments p JOIN water_orders wo ON p.order_id=wo.id JOIN vendors v ON wo.vendor_id=v.id WHERE wo.customer_id=? ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]); $payments = $stmt->fetchAll();
?>
<div class="table-container">
    <div class="table-header"><h3>💳 Payment History</h3></div>
    <table class="data-table"><thead><tr><th>Order #</th><th>Vendor</th><th>Amount</th><th>Method</th><th>M-Pesa Code</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php if(empty($payments)): ?><tr><td colspan="7" class="no-data">No payment records yet.</td></tr>
    <?php else: foreach($payments as $p): ?>
    <tr>
        <td><strong>#<?php echo $p['order_id']; ?></strong></td>
        <td><?php echo sanitize($p['business_name']); ?></td>
        <td><?php echo formatCurrency($p['amount']); ?></td>
        <td><span class="status-badge <?php echo $p['payment_method']==='mpesa'?'badge-success':'badge-info'; ?>"><?php echo strtoupper($p['payment_method']); ?></span></td>
        <td><?php echo $p['mpesa_code'] ? sanitize($p['mpesa_code']) : '—'; ?></td>
        <td><?php echo getStatusBadge($p['status']); ?></td>
        <td><?php echo formatDate($p['created_at']); ?></td>
    </tr>
    <?php endforeach; endif; ?>
    </tbody></table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
