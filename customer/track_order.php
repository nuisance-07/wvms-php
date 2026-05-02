<?php
$pageTitle = 'Track Order';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

$orderId = (int)($_GET['id'] ?? 0);
if ($orderId) {
    $stmt = $db->prepare("SELECT wo.*, v.business_name, d.id as delivery_id, d.scheduled_time, d.actual_delivery_time, d.delivery_notes, d.status as delivery_status FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id LEFT JOIN deliveries d ON d.order_id=wo.id WHERE wo.id=? AND wo.customer_id=?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
} else {
    $stmt = $db->prepare("SELECT wo.*, v.business_name, d.id as delivery_id, d.scheduled_time, d.actual_delivery_time, d.delivery_notes, d.status as delivery_status FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id LEFT JOIN deliveries d ON d.order_id=wo.id WHERE wo.customer_id=? AND wo.status NOT IN('delivered','cancelled') ORDER BY wo.created_at DESC LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $order = $stmt->fetch();
}

$steps = ['pending'=>1,'accepted'=>2,'dispatched'=>3,'delivered'=>4];
$currentStep = $order ? ($steps[$order['status']] ?? 0) : 0;

$extraScripts = '';
if ($order && $order['status'] === 'dispatched' && !empty($order['delivery_id'])) {
    $extraScripts = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>#map { height: 300px; width: 100%; border-radius: 8px; margin-top: 16px; border: 1px solid var(--border); }</style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        var map = L.map("map").setView([-1.286389, 36.817223], 14); // Nairobi default
        L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", { maxZoom: 19, attribution: "© OpenStreetMap" }).addTo(map);
        var truckIcon = L.icon({
            iconUrl: "https://cdn-icons-png.flaticon.com/512/3256/3256157.png",
            iconSize: [40, 40], iconAnchor: [20, 20]
        });
        var marker = L.marker([-1.286389, 36.817223], {icon: truckIcon}).addTo(map);
        
        function fetchLocation() {
            fetch("/api/get_location.php?delivery_id='.$order['delivery_id'].'")
            .then(r => r.json())
            .then(data => {
                if (data.success && data.lat && data.lng) {
                    var newLatLng = new L.LatLng(data.lat, data.lng);
                    marker.setLatLng(newLatLng);
                    map.panTo(newLatLng);
                }
            }).catch(e => console.error("Error fetching GPS:", e));
        }
        setInterval(fetchLocation, 3000);
        fetchLocation();
    });
    </script>';
}
?>

<?php if ($order): ?>
<div class="card fade-in">
    <div class="flex justify-between items-center mb-6">
        <h3>Order #<?php echo $order['id']; ?></h3>
        <?php echo getStatusBadge($order['status']); ?>
    </div>

    <div class="stepper-container">
        <div class="stepper">
            <?php $stepLabels = ['Pending','Accepted','Dispatched','Delivered'];
            foreach($stepLabels as $i => $label):
                $stepNum = $i + 1;
                $class = $currentStep > $stepNum ? 'completed' : ($currentStep == $stepNum ? 'active' : '');
            ?>
            <div class="stepper-step <?php echo $class; ?>">
                <div class="step-circle"><?php echo $currentStep > $stepNum ? '✓' : $stepNum; ?></div>
                <div class="step-label"><?php echo $label; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid-2" style="margin-top:24px">
        <div class="card" style="box-shadow:none; margin-bottom:0">
            <h4 class="mb-4" style="color:var(--text-primary)">Order Details</h4>
            <div style="display:flex;flex-direction:column;gap:12px;font-size:0.875rem">
                <div><span class="label">Vendor</span><br><strong><?php echo sanitize($order['business_name']); ?></strong></div>
                <div><span class="label">Quantity</span><br><strong><?php echo $order['quantity_litres']; ?> Litres</strong></div>
                <div><span class="label">Total Amount</span><br><strong><?php echo formatCurrency($order['total_amount']); ?></strong></div>
                <div><span class="label">Delivery Address</span><br><strong><?php echo sanitize($order['delivery_address']); ?></strong></div>
            </div>
        </div>
        <div class="card" style="box-shadow:none; margin-bottom:0">
            <h4 class="mb-4" style="color:var(--text-primary)">Delivery Info</h4>
            <div style="display:flex;flex-direction:column;gap:12px;font-size:0.875rem">
                <div><span class="label">Ordered At</span><br><strong><?php echo formatDateTime($order['created_at']); ?></strong></div>
                <div><span class="label">Scheduled Time</span><br><strong><?php echo $order['scheduled_time'] ? formatDateTime($order['scheduled_time']) : 'To be determined'; ?></strong></div>
                <div><span class="label">Delivered At</span><br><strong><?php echo $order['actual_delivery_time'] ? formatDateTime($order['actual_delivery_time']) : '—'; ?></strong></div>
                <?php if($order['delivery_notes']): ?>
                <div><span class="label">Notes</span><br><strong><?php echo sanitize($order['delivery_notes']); ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($order['status']==='delivered'): ?>
    <div style="margin-top:32px;text-align:center">
        <a href="/customer/feedback.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">⭐ Rate This Delivery</a>
    </div>
    <?php elseif($order['status']==='dispatched' && !empty($order['delivery_id'])): ?>
    <div class="card" style="margin-top:24px; box-shadow:none">
        <h4 style="color:var(--text-primary)">📍 Live Tracking</h4>
        <p style="font-size:0.875rem; color:var(--text-secondary)">Your water is on the way! The driver\'s location updates every few seconds.</p>
        <div id="map"></div>
    </div>
    <?php endif; ?>
</div>

<script>
<?php if ($order['status'] !== 'delivered' && $order['status'] !== 'cancelled' && $order['status'] !== 'dispatched'): ?>
setTimeout(()=>location.reload(), 30000);
<?php endif; ?>
</script>
<?php else: ?>
<div class="empty-state"><div class="icon">📍</div><h3>No Active Order</h3><p>You don't have any active orders to track.</p><a href="/customer/place_order.php" class="btn btn-primary">Place an Order</a></div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
