<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'customer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$deliveryId = (int)($_GET['delivery_id'] ?? 0);

if ($deliveryId < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid delivery ID']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT d.current_lat, d.current_lng, d.status FROM deliveries d JOIN water_orders wo ON d.order_id = wo.id WHERE d.id = ? AND wo.customer_id = ?");
$stmt->execute([$deliveryId, $_SESSION['user_id']]);
$location = $stmt->fetch();

if (!$location) {
    echo json_encode(['success' => false, 'message' => 'Delivery not found']);
    exit;
}

echo json_encode([
    'success' => true, 
    'lat' => $location['current_lat'], 
    'lng' => $location['current_lng'],
    'status' => $location['status']
]);
