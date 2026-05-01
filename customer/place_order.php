<?php
$pageTitle = 'Place Order';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();
$vendors = getActiveVendors();
$user = getCurrentUserFull();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    $vendor_id = (int)($_POST['vendor_id']??0);
    $qty = (int)($_POST['quantity_litres']??0);
    $address = sanitize($_POST['delivery_address']??'');
    $time = $_POST['preferred_delivery_time']??null;
    $price = (float)($_POST['unit_price']??5.00);

    if ($vendor_id<1||$qty<1||empty($address)) {
        setFlash('error','Please fill in all required fields.');
    } else {
        $stmt = $db->prepare("INSERT INTO water_orders (customer_id,vendor_id,quantity_litres,unit_price,delivery_address,preferred_delivery_time) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$_SESSION['user_id'],$vendor_id,$qty,$price,$address,$time?:null]);
        setFlash('success','Order placed successfully!');
        redirect('/customer/orders.php');
    }
}
?>

<div class="card">
    <div class="card-header"><h3>🛒 New Water Order</h3></div>
    <form method="POST">
        <?php csrfField(); ?>
        <div class="form-group">
            <label for="vendor_id">Select Vendor</label>
            <select name="vendor_id" id="vendor_id" class="form-control" required>
                <option value="">— Choose a vendor —</option>
                <?php foreach($vendors as $v): ?>
                <option value="<?php echo $v['id']; ?>"><?php echo sanitize($v['business_name']); ?> (<?php echo sanitize($v['service_area']); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Quick Select Quantity</label>
            <div class="preset-buttons">
                <button type="button" class="preset-btn" onclick="setPreset(20)">20L</button>
                <button type="button" class="preset-btn" onclick="setPreset(50)">50L</button>
                <button type="button" class="preset-btn" onclick="setPreset(100)">100L</button>
                <button type="button" class="preset-btn" onclick="setPreset(200)">200L</button>
                <button type="button" class="preset-btn" onclick="setPreset(500)">500L</button>
                <button type="button" class="preset-btn" onclick="setPreset(1000)">1000L</button>
            </div>
            <input type="number" id="quantity_litres" name="quantity_litres" class="form-control" placeholder="Or enter custom quantity" min="1" required>
        </div>

        <div class="form-group">
            <label for="unit_price">Price per Litre (KES)</label>
            <input type="number" id="unit_price" name="unit_price" class="form-control" value="5" min="1" step="0.50">
        </div>

        <div class="form-group">
            <label for="delivery_address">Delivery Address</label>
            <input type="text" id="delivery_address" name="delivery_address" class="form-control" placeholder="e.g. House 12, Umoja Estate" value="<?php echo sanitize($user['location']??''); ?>" required>
        </div>

        <div class="form-group">
            <label for="preferred_delivery_time">Preferred Delivery Time (optional)</label>
            <input type="datetime-local" id="preferred_delivery_time" name="preferred_delivery_time" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:16px">📦 Place Order</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
