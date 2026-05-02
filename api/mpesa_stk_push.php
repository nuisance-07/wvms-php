<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$paymentId = (int)($_POST['payment_id'] ?? 0);
$phone = $_POST['phone'] ?? '';

if ($paymentId < 1 || empty($phone)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment details or phone number.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT p.id, p.amount FROM payments p JOIN water_orders wo ON p.order_id = wo.id WHERE p.id = ? AND wo.customer_id = ? AND p.status = 'pending'");
$stmt->execute([$paymentId, $_SESSION['user_id']]);
$payment = $stmt->fetch();

if (!$payment) {
    echo json_encode(['success' => false, 'message' => 'Payment not found or already processed.']);
    exit;
}

// SIMULATE DARAJA STK PUSH API CALL
// In a real app, you would use cURL to hit Safaricom's endpoint with OAuth token.
// We'll just return success.
echo json_encode(['success' => true, 'message' => 'STK Push sent successfully to ' . sanitize($phone) . '. Please check your phone.']);
