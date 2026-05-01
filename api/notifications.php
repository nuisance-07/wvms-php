<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['error'=>'Unauthorized']); exit; }
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $action = $_POST['action']??'';
    if ($action==='mark_read') {
        $id=(int)($_POST['id']??0);
        markNotificationRead($id, $_SESSION['user_id']);
        echo json_encode(['success'=>true]);
    } elseif ($action==='mark_all_read') {
        markAllNotificationsRead($_SESSION['user_id']);
        echo json_encode(['success'=>true]);
    }
} else {
    $notifs = getNotifications($_SESSION['user_id'], 10);
    $result = [];
    foreach($notifs as $n) {
        $result[] = ['id'=>$n['id'],'message'=>$n['message'],'type'=>$n['type'],'is_read'=>(bool)$n['is_read'],'time_ago'=>timeAgo($n['created_at'])];
    }
    echo json_encode($result);
}
