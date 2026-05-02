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

<div class="fade-in">
    <!-- Step Indicator -->
    <div class="stepper-container" style="background:var(--surface); border-radius:12px; padding:24px; border:1px solid var(--border); box-shadow:var(--shadow-sm);">
        <div class="stepper" id="orderStepper">
            <div class="stepper-step active" id="indicator-1">
                <div class="step-circle">1</div>
                <div class="step-label">Order Details</div>
            </div>
            <div class="stepper-step" id="indicator-2">
                <div class="step-circle">2</div>
                <div class="step-label">Delivery Info</div>
            </div>
            <div class="stepper-step" id="indicator-3">
                <div class="step-circle">3</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>
    </div>

    <form method="POST" id="orderForm" style="margin-top:24px">
        <?php csrfField(); ?>
        
        <!-- Step 1: Details -->
        <div class="card fade-in" id="step-1">
            <h3 class="mb-4">Step 1: Order Details</h3>
            <div class="form-group">
                <label class="form-label" for="vendor_id">Select Vendor</label>
                <select name="vendor_id" id="vendor_id" class="form-control" required onchange="updateSummary()">
                    <option value="">— Choose a vendor —</option>
                    <?php foreach($vendors as $v): ?>
                    <option value="<?php echo $v['id']; ?>" data-name="<?php echo sanitize($v['business_name']); ?>"><?php echo sanitize($v['business_name']); ?> (<?php echo sanitize($v['service_area']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Quick Select Quantity</label>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
                    <?php foreach([20,50,100,200,500,1000] as $q): ?>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('quantity_litres').value=<?php echo $q; ?>;updateSummary()"><?php echo $q; ?>L</button>
                    <?php endforeach; ?>
                </div>
                <input type="number" id="quantity_litres" name="quantity_litres" class="form-control" placeholder="Or enter custom quantity" min="1" required onkeyup="updateSummary()" onchange="updateSummary()">
            </div>
            
            <div style="text-align:right">
                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next: Delivery Info →</button>
            </div>
        </div>

        <!-- Step 2: Delivery -->
        <div class="card fade-in" id="step-2" style="display:none">
            <h3 class="mb-4">Step 2: Delivery Information</h3>
            <div class="form-group">
                <label class="form-label" for="delivery_address">Delivery Address</label>
                <input type="text" id="delivery_address" name="delivery_address" class="form-control" placeholder="e.g. House 12, Umoja Estate" value="<?php echo sanitize($user['location']??''); ?>" required onkeyup="updateSummary()">
            </div>

            <div class="form-group">
                <label class="form-label" for="preferred_delivery_time">Preferred Delivery Time (optional)</label>
                <input type="datetime-local" id="preferred_delivery_time" name="preferred_delivery_time" class="form-control" onchange="updateSummary()">
            </div>

            <div class="form-group">
                <label class="form-label" for="unit_price">Price per Litre (KES)</label>
                <input type="number" id="unit_price" name="unit_price" class="form-control" value="5" min="1" step="0.50" onkeyup="updateSummary()" onchange="updateSummary()">
            </div>
            
            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="prevStep(1)">← Back</button>
                <button type="button" class="btn btn-primary" onclick="nextStep(3)">Next: Confirm Order →</button>
            </div>
        </div>

        <!-- Step 3: Confirm -->
        <div class="card fade-in" id="step-3" style="display:none">
            <h3 class="mb-4">Step 3: Confirm Order</h3>
            
            <div style="background:var(--surface-3); padding:20px; border-radius:12px; margin-bottom:24px">
                <div class="grid-2" style="gap:16px; font-size:0.875rem">
                    <div><span style="color:var(--text-secondary)">Vendor:</span><br><strong id="sum-vendor" style="font-size:1rem">—</strong></div>
                    <div><span style="color:var(--text-secondary)">Quantity:</span><br><strong id="sum-qty" style="font-size:1rem">0L</strong></div>
                    <div><span style="color:var(--text-secondary)">Address:</span><br><strong id="sum-address" style="font-size:1rem">—</strong></div>
                    <div><span style="color:var(--text-secondary)">Total Estimated Cost:</span><br><strong id="sum-total" style="font-size:1.25rem;color:var(--primary)">KES 0.00</strong></div>
                </div>
            </div>

            <div class="flex justify-between">
                <button type="button" class="btn btn-secondary" onclick="prevStep(2)">← Edit Details</button>
                <button type="submit" id="submitBtn" class="btn btn-success" onclick="this.classList.add('btn-loading')">✅ Submit Order</button>
            </div>
        </div>
    </form>
</div>

<script>
function nextStep(step) {
    if(step === 2) {
        if(!document.getElementById('vendor_id').value || !document.getElementById('quantity_litres').value) {
            alert('Please select a vendor and quantity.'); return;
        }
    }
    if(step === 3) {
        if(!document.getElementById('delivery_address').value) {
            alert('Please provide a delivery address.'); return;
        }
    }
    
    document.getElementById('step-1').style.display = 'none';
    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-3').style.display = 'none';
    document.getElementById('step-'+step).style.display = 'block';

    document.getElementById('indicator-1').className = 'stepper-step ' + (step > 1 ? 'completed' : (step==1?'active':''));
    document.getElementById('indicator-2').className = 'stepper-step ' + (step > 2 ? 'completed' : (step==2?'active':''));
    document.getElementById('indicator-3').className = 'stepper-step ' + (step==3?'active':'');
    
    updateSummary();
}
function prevStep(step) {
    document.getElementById('step-1').style.display = 'none';
    document.getElementById('step-2').style.display = 'none';
    document.getElementById('step-3').style.display = 'none';
    document.getElementById('step-'+step).style.display = 'block';

    document.getElementById('indicator-1').className = 'stepper-step ' + (step > 1 ? 'completed' : (step==1?'active':''));
    document.getElementById('indicator-2').className = 'stepper-step ' + (step > 2 ? 'completed' : (step==2?'active':''));
    document.getElementById('indicator-3').className = 'stepper-step ' + (step==3?'active':'');
}
function updateSummary() {
    let sel = document.getElementById('vendor_id');
    let vendorName = sel.options[sel.selectedIndex]?.getAttribute('data-name') || '—';
    document.getElementById('sum-vendor').innerText = vendorName;
    
    let qty = parseFloat(document.getElementById('quantity_litres').value) || 0;
    document.getElementById('sum-qty').innerText = qty + 'L';
    
    document.getElementById('sum-address').innerText = document.getElementById('delivery_address').value || '—';
    
    let price = parseFloat(document.getElementById('unit_price').value) || 0;
    document.getElementById('sum-total').innerText = 'KES ' + (qty * price).toFixed(2);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
