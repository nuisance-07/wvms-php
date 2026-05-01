<?php
$pageTitle = 'Track Order';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId) {
    $stmt = $db->prepare("SELECT wo.*, v.business_name, d.scheduled_time, d.actual_delivery_time, d.delivery_notes, d.status as delivery_status FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id LEFT JOIN deliveries d ON d.order_id=wo.id WHERE wo.id=? AND wo.customer_id=?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
} else {
    $stmt = $db->prepare("SELECT wo.*, v.business_name, d.scheduled_time, d.actual_delivery_time, d.delivery_notes, d.status as delivery_status FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id LEFT JOIN deliveries d ON d.order_id=wo.id WHERE wo.customer_id=? AND wo.status NOT IN('delivered','cancelled') ORDER BY wo.created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $order = $stmt->fetch();
}

$steps = ['pending'=>1,'accepted'=>2,'dispatched'=>3,'delivered'=>4];
$currentStep = $order ? ($steps[$order['status']] ?? 0) : 0;
?>

<?php if ($order): ?>
<div class="card">
    <div class="card-header"><h3>Order #<?php echo $order['id']; ?></h3><?php echo getStatusBadge($order['status']); ?></div>

    <div class="stepper">
        <?php $stepLabels = ['Pending','Accepted','Dispatched','Delivered'];
        $stepIcons = ['📝','✅','🚚','🏠'];
        foreach($stepLabels as $i => $label):
            $stepNum = $i + 1;
            $class = $currentStep > $stepNum ? 'completed' : ($currentStep == $stepNum ? 'active' : '');
        ?>
        <div class="stepper-step <?php echo $class; ?>">
            <div class="stepper-circle"><?php echo $currentStep > $stepNum ? '✓' : $stepIcons[$i]; ?></div>
            <span class="stepper-label"><?php echo $label; ?></span>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:24px">
        <div class="card" style="margin:0"><h4 style="margin-bottom:12px;color:var(--primary)">Order Details</h4>
            <p><strong>Vendor:</strong> <?php echo sanitize($order['business_name']); ?></p>
            <p><strong>Quantity:</strong> <?php echo $order['quantity_litres']; ?> Litres</p>
            <p><strong>Total:</strong> <?php echo formatCurrency($order['total_amount']); ?></p>
            <p><strong>Address:</strong> <?php echo sanitize($order['delivery_address']); ?></p>
        </div>
        <div class="card" style="margin:0"><h4 style="margin-bottom:12px;color:var(--primary)">Delivery Info</h4>
            <p><strong>Ordered:</strong> <?php echo formatDateTime($order['created_at']); ?></p>
            <p><strong>Scheduled:</strong> <?php echo $order['scheduled_time'] ? formatDateTime($order['scheduled_time']) : 'TBD'; ?></p>
            <p><strong>Delivered:</strong> <?php echo $order['actual_delivery_time'] ? formatDateTime($order['actual_delivery_time']) : '—'; ?></p>
            <?php if($order['delivery_notes']): ?><p><strong>Notes:</strong> <?php echo sanitize($order['delivery_notes']); ?></p><?php endif; ?>
        </div>
    </div>

    <?php if($order['status']==='delivered'): ?>
    <div style="margin-top:20px;text-align:center">
        <a href="/customer/feedback.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">⭐ Rate This Delivery</a>
    </div>
    <?php endif; ?>
</div>

<script>
<?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled'): ?>
setTimeout(()=>location.reload(), 30000);
<?php endif; ?>
</script>
<?php else: ?>
<div class="empty-state"><div class="icon">📍</div><h3>No Active Order</h3><p>You don't have any active orders to track.</p><a href="/customer/place_order.php" class="btn btn-primary">Place an Order</a></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
