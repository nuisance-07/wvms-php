<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()||$_SERVER['REQUEST_METHOD']!=='POST') { echo json_encode(['error'=>'Invalid']); exit; }
$db = getDB();
$pid=(int)($_POST['payment_id']??0); $method=$_POST['payment_method']??'cash'; $mpesa=sanitize($_POST['mpesa_code']??'');
if(!$pid||!in_array($method,['cash','mpesa'])) { echo json_encode(['error'=>'Bad request']); exit; }
$db->prepare("UPDATE payments SET payment_method=?,mpesa_code=?,status='confirmed',confirmed_at=NOW() WHERE id=?")->execute([$method,$method==='mpesa'?$mpesa:null,$pid]);
echo json_encode(['success'=>true]);
