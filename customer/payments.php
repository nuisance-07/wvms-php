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
                    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;background:var(--surface-3);padding:8px;border-radius:8px;border:1px solid var(--border)" id="pay-box-<?php echo $p['id']; ?>">
                        <span style="font-size:1.2rem">📱</span>
                        <input type="text" id="phone-<?php echo $p['id']; ?>" placeholder="Phone (e.g. 0712345678)" class="form-control" style="flex:1;min-width:140px;padding:8px;font-size:0.875rem" value="<?php echo sanitize(getCurrentUserFull()['phone']); ?>" required>
                        <button class="btn btn-success btn-sm" onclick="triggerSTKPush(<?php echo $p['id']; ?>)">Pay via M-Pesa</button>
                    </div>
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
<script>
function triggerSTKPush(paymentId) {
    let phone = document.getElementById('phone-' + paymentId).value;
    if(!phone) { alert('Enter phone number'); return; }
    
    let box = document.getElementById('pay-box-' + paymentId);
    box.innerHTML = '<span class="text-primary">⏳ Initiating M-Pesa STK Push...</span>';
    
    let formData = new FormData();
    formData.append('payment_id', paymentId);
    formData.append('phone', phone);
    
    fetch('/api/mpesa_stk_push.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            box.innerHTML = '<span class="text-success">📱 Please check your phone and enter M-Pesa PIN.</span>';
            // Simulate Safaricom Daraja Webhook Callback after 5 seconds
            setTimeout(() => {
                box.innerHTML = '<span class="text-success">✅ Payment confirmed! Refreshing...</span>';
                fetch('/api/mpesa_callback.php?id=' + paymentId).then(() => window.location.reload());
            }, 5000);
        } else {
            box.innerHTML = '<span class="text-danger">❌ ' + data.message + '</span>';
        }
    }).catch(e => {
        box.innerHTML = '<span class="text-danger">❌ Network Error</span>';
    });
}
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
