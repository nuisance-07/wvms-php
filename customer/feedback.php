<?php
$pageTitle = 'Feedback';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCSRF();
    $oid = (int)($_POST['order_id']??0);
    $rating = (int)($_POST['rating']??0);
    $comment = sanitize($_POST['comment']??'');
    if ($oid && $rating>=1 && $rating<=5) {
        $check = $db->prepare("SELECT id FROM feedback WHERE order_id=?"); $check->execute([$oid]);
        if (!$check->fetch()) {
            $stmt = $db->prepare("INSERT INTO feedback (order_id,customer_id,rating,comment) VALUES (?,?,?,?)");
            $stmt->execute([$oid,$_SESSION['user_id'],$rating,$comment]);
            setFlash('success','Thank you for your feedback!');
        } else { setFlash('warning','You already rated this order.'); }
    } else { setFlash('error','Please select a rating.'); }
    redirect('/customer/feedback.php');
}

$orderIdParam = (int)($_GET['order_id']??0);

// Orders eligible for feedback (delivered, no existing feedback)
$eligible = $db->prepare("SELECT wo.id, wo.quantity_litres, wo.created_at, v.business_name FROM water_orders wo JOIN vendors v ON wo.vendor_id=v.id LEFT JOIN feedback f ON f.order_id=wo.id WHERE wo.customer_id=? AND wo.status='delivered' AND f.id IS NULL ORDER BY wo.created_at DESC");
$eligible->execute([$_SESSION['user_id']]); $eligibleOrders = $eligible->fetchAll();

// Past feedback
$past = $db->prepare("SELECT f.*, wo.quantity_litres, v.business_name FROM feedback f JOIN water_orders wo ON f.order_id=wo.id JOIN vendors v ON wo.vendor_id=v.id WHERE f.customer_id=? ORDER BY f.created_at DESC");
$past->execute([$_SESSION['user_id']]); $pastFeedback = $past->fetchAll();
?>

<?php if(!empty($eligibleOrders)): ?>
<div class="card">
    <div class="card-header"><h3>⭐ Rate a Delivery</h3></div>
    <form method="POST">
        <?php csrfField(); ?>
        <div class="form-group"><label>Select Order</label>
            <select name="order_id" class="form-control" required>
                <?php foreach($eligibleOrders as $e): ?>
                <option value="<?php echo $e['id']; ?>" <?php echo $orderIdParam==$e['id']?'selected':''; ?>>Order #<?php echo $e['id']; ?> — <?php echo sanitize($e['business_name']); ?> (<?php echo $e['quantity_litres']; ?>L, <?php echo formatDate($e['created_at']); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Rating</label>
            <div class="star-rating">
                <?php for($i=5;$i>=1;$i--): ?>
                <input type="radio" name="rating" id="star<?php echo $i; ?>" value="<?php echo $i; ?>">
                <label for="star<?php echo $i; ?>">★</label>
                <?php endfor; ?>
            </div>
        </div>
        <div class="form-group"><label>Comment (optional)</label><textarea name="comment" class="form-control" placeholder="How was your experience?"></textarea></div>
        <button type="submit" class="btn btn-primary">Submit Feedback</button>
    </form>
</div>
<?php endif; ?>

<div class="table-container">
    <div class="table-header"><h3>Past Feedback</h3></div>
    <table class="data-table"><thead><tr><th>Order</th><th>Vendor</th><th>Rating</th><th>Comment</th><th>Date</th></tr></thead><tbody>
    <?php if(empty($pastFeedback)): ?><tr><td colspan="5" class="no-data">No feedback given yet.</td></tr>
    <?php else: foreach($pastFeedback as $f): ?>
    <tr><td>#<?php echo $f['order_id']; ?></td><td><?php echo sanitize($f['business_name']); ?></td><td><?php echo renderStars($f['rating']); ?></td><td><?php echo sanitize($f['comment'])?:'-'; ?></td><td><?php echo formatDate($f['created_at']); ?></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
