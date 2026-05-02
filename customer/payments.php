<?php
$pageTitle = 'Payments';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    $pid = (int)($_POST['payment_id'] ?? 0);
    $mpesa = sanitize($_POST['mpesa_code'] ?? '');
    
    if ($pid && $mpesa) {
        $db->prepare("UPDATE payments SET payment_method='mpesa', mpesa_code=? WHERE id=? AND status='pending'")->execute([$mpesa, $pid]);
        setFlash('success', 'M-Pesa transaction code submitted! Waiting for vendor confirmation.');
        redirect('/customer/payments.php');
    }
}

$stmt = $db->prepare("SELECT p.*, wo.quantity_litres, wo.total_amount as order_total, v.business_name FROM payments p JOIN water_orders wo ON p.order_id=wo.id JOIN vendors v ON wo.vendor_id=v.id WHERE wo.customer_id=? ORDER BY p.created_at DESC");
$stmt->execute([$_SESSION['user_id']]); $payments = $stmt->fetchAll();
?>
<div class="table-wrapper fade-in">
    <div class="table-header-row">
        <div class="table-title">💳 Payment History</div>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>Order #</th><th>Vendor</th><th>Amount</th><th>Method</th><th>M-Pesa Code</th><th>Status</th><th>Date</th></tr>
        </thead>
        <tbody>
        <?php if(empty($payments)): ?>
            <tr><td colspan="7" class="no-data">No payment records yet.</td></tr>
        <?php else: foreach($payments as $p): ?>
        <tr>
            <td><strong>#<?php echo $p['order_id']; ?></strong></td>
            <td><?php echo sanitize($p['business_name']); ?></td>
            <td><?php echo formatCurrency($p['amount']); ?></td>
            <td><span class="badge <?php echo $p['payment_method']==='mpesa'?'badge-success':'badge-info'; ?>"><?php echo strtoupper($p['payment_method']); ?></span></td>
            
            <?php if ($p['status'] === 'pending' && empty($p['mpesa_code'])): ?>
                <td colspan="2">
                    <form method="POST" style="display:flex;gap:8px;align-items:center;background:var(--surface-3);padding:8px;border-radius:8px;border:1px solid var(--border)">
                        <?php csrfField(); ?><input type="hidden" name="payment_id" value="<?php echo $p['id']; ?>">
                        <span style="font-size:1.2rem">📱</span>
                        <input type="text" name="mpesa_code" placeholder="Enter M-Pesa Code" class="form-control" style="width:160px;padding:8px;font-size:0.875rem" required>
                        <button class="btn btn-success btn-sm">Submit</button>
                    </form>
                </td>
            <?php else: ?>
                <td><span style="font-family:monospace"><?php echo $p['mpesa_code'] ? sanitize($p['mpesa_code']) : '—'; ?></span></td>
                <td><?php echo getStatusBadge($p['status']); ?></td>
            <?php endif; ?>
            
            <td><?php echo formatDate($p['created_at']); ?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
