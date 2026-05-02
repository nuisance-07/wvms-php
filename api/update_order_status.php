<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()||$_SERVER['REQUEST_METHOD']!=='POST') { echo json_encode(['error'=>'Invalid']); exit; }
$db = getDB();
$orderId=(int)($_POST['order_id']??0); $newStatus=$_POST['status']??'';
$vendor = getVendorByUserId($_SESSION['user_id']);
if(!$vendor||!$orderId||!in_array($newStatus,['accepted','dispatched','delivered','cancelled'])) { echo json_encode(['error'=>'Bad request']); exit; }
$db->prepare("UPDATE water_orders SET status=? WHERE id=? AND vendor_id=?")->execute([$newStatus,$orderId,$vendor['id']]);

// Fetch customer details to send SMS
$stmt = $db->prepare("SELECT u.id, u.phone, u.name FROM water_orders wo JOIN users u ON wo.customer_id = u.id WHERE wo.id = ?");
$stmt->execute([$orderId]);
$customer = $stmt->fetch();

if ($customer) {
    createNotification($customer['id'], "Your order #{$orderId} is now {$newStatus}.", 'order');
    $smsMessage = "Hi {$customer['name']}, your water order #{$orderId} from {$vendor['business_name']} is now {$newStatus}.";
    sendSMS($customer['phone'], $smsMessage);
}

echo json_encode(['success'=>true,'status'=>$newStatus]);
