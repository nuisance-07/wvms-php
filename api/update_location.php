<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (!isLoggedIn() || $_SESSION['role'] !== 'vendor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$deliveryId = (int)($_POST['delivery_id'] ?? 0);
$lat = (float)($_POST['lat'] ?? 0);
$lng = (float)($_POST['lng'] ?? 0);

if ($deliveryId < 1 || !$lat || !$lng) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

$vendor = getVendorByUserId($_SESSION['user_id']);
$db = getDB();

$stmt = $db->prepare("UPDATE deliveries SET current_lat = ?, current_lng = ? WHERE id = ? AND vendor_id = ?");
$stmt->execute([$lat, $lng, $deliveryId, $vendor['id']]);

echo json_encode(['success' => true]);
