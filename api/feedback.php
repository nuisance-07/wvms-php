<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()||$_SERVER['REQUEST_METHOD']!=='POST') { echo json_encode(['error'=>'Invalid']); exit; }
$db = getDB();
$oid=(int)($_POST['order_id']??0); $rating=(int)($_POST['rating']??0); $comment=sanitize($_POST['comment']??'');
if(!$oid||$rating<1||$rating>5) { echo json_encode(['error'=>'Invalid rating']); exit; }
$check=$db->prepare("SELECT id FROM feedback WHERE order_id=?"); $check->execute([$oid]);
if($check->fetch()) { echo json_encode(['error'=>'Already rated']); exit; }
$db->prepare("INSERT INTO feedback (order_id,customer_id,rating,comment) VALUES (?,?,?,?)")->execute([$oid,$_SESSION['user_id'],$rating,$comment]);
echo json_encode(['success'=>true]);
