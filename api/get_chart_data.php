<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['error'=>'Unauthorized']); exit; }
$db = getDB();

$period = $_GET['period'] ?? 'daily';
$vendorId = (int)($_GET['vendor_id'] ?? 0);

$vendorWhere = $vendorId ? "AND wo.vendor_id = $vendorId" : "";

if ($period === 'daily') {
    $revQuery = "SELECT DATE(p.confirmed_at) as label, SUM(p.amount) as value FROM payments p JOIN water_orders wo ON p.order_id=wo.id WHERE p.status='confirmed' AND p.confirmed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $vendorWhere GROUP BY DATE(p.confirmed_at) ORDER BY label";
    $ordQuery = "SELECT DATE(wo.created_at) as label, COUNT(*) as value FROM water_orders wo WHERE wo.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) $vendorWhere GROUP BY DATE(wo.created_at) ORDER BY label";
} elseif ($period === 'weekly') {
    $revQuery = "SELECT YEARWEEK(p.confirmed_at) as label, SUM(p.amount) as value FROM payments p JOIN water_orders wo ON p.order_id=wo.id WHERE p.status='confirmed' AND p.confirmed_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK) $vendorWhere GROUP BY YEARWEEK(p.confirmed_at) ORDER BY label";
    $ordQuery = "SELECT YEARWEEK(wo.created_at) as label, COUNT(*) as value FROM water_orders wo WHERE wo.created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK) $vendorWhere GROUP BY YEARWEEK(wo.created_at) ORDER BY label";
} else {
    $revQuery = "SELECT DATE_FORMAT(p.confirmed_at,'%Y-%m') as label, SUM(p.amount) as value FROM payments p JOIN water_orders wo ON p.order_id=wo.id WHERE p.status='confirmed' AND p.confirmed_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) $vendorWhere GROUP BY DATE_FORMAT(p.confirmed_at,'%Y-%m') ORDER BY label";
    $ordQuery = "SELECT DATE_FORMAT(wo.created_at,'%Y-%m') as label, COUNT(*) as value FROM water_orders wo WHERE wo.created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) $vendorWhere GROUP BY DATE_FORMAT(wo.created_at,'%Y-%m') ORDER BY label";
}

$rev = $db->query($revQuery)->fetchAll();
$ord = $db->query($ordQuery)->fetchAll();

echo json_encode([
    'revenue' => ['labels'=>array_column($rev,'label'),'data'=>array_map('floatval',array_column($rev,'value'))],
    'orders'  => ['labels'=>array_column($ord,'label'),'data'=>array_map('intval',array_column($ord,'value'))]
]);
