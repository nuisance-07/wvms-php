<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

// This simulates the Safaricom Daraja Webhook Callback.
// In reality, Daraja sends a POST request with JSON to this URL.
// Since this is a mock, we'll allow the frontend to trigger it directly.

$paymentId = (int)($_GET['id'] ?? 0);

if ($paymentId < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment ID.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT p.id, p.amount, p.order_id, wo.customer_id FROM payments p JOIN water_orders wo ON p.order_id = wo.id WHERE p.id = ?");
$stmt->execute([$paymentId]);
$payment = $stmt->fetch();

if (!$payment) {
    echo json_encode(['success' => false, 'message' => 'Payment not found.']);
    exit;
}

// Generate a random M-Pesa Receipt Number (e.g. QAZ123WSX)
$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
$mpesaCode = substr(str_shuffle($chars), 0, 10);

// Update payment to confirmed
$db->prepare("UPDATE payments SET status = 'confirmed', payment_method = 'mpesa', mpesa_code = ?, confirmed_at = CURRENT_TIMESTAMP WHERE id = ?")
   ->execute([$mpesaCode, $paymentId]);

// Create a notification for the customer
createNotification($payment['customer_id'], "Payment of KES {$payment['amount']} confirmed via M-Pesa (Code: {$mpesaCode}).", "payment");

echo json_encode(['success' => true, 'message' => 'Payment confirmed via mock callback.']);
