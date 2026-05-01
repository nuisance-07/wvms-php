<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
requireRole('customer');
$db = getDB();
$user = getCurrentUserFull();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCSRF();
    $action = $_POST['action'] ?? 'update';
    if ($action === 'update') {
        $name = sanitize($_POST['name']??'');
        $phone = sanitize($_POST['phone']??'');
        $location = sanitize($_POST['location']??'');
        if ($name && $phone) {
            $stmt = $db->prepare("UPDATE users SET name=?,phone=?,location=? WHERE id=?");
            $stmt->execute([$name,$phone,$location,$_SESSION['user_id']]);
            $_SESSION['user_name'] = $name;
            setFlash('success','Profile updated!');
        }
    } elseif ($action === 'password') {
        $current = $_POST['current_password']??'';
        $new = $_POST['new_password']??'';
        $confirm = $_POST['confirm_password']??'';
        $stmt = $db->prepare("SELECT password FROM users WHERE id=?"); $stmt->execute([$_SESSION['user_id']]); $u = $stmt->fetch();
        if (!password_verify($current, $u['password'])) { setFlash('error','Current password is incorrect.'); }
        elseif (strlen($new)<6) { setFlash('error','New password must be at least 6 characters.'); }
        elseif ($new!==$confirm) { setFlash('error','Passwords do not match.'); }
        else { $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new,PASSWORD_BCRYPT),$_SESSION['user_id']]); setFlash('success','Password changed!'); }
    }
    redirect('/customer/profile.php');
}
?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
<div class="card"><div class="card-header"><h3>👤 Profile Info</h3></div>
<form method="POST"><?php csrfField(); ?><input type="hidden" name="action" value="update">
<div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" value="<?php echo sanitize($user['name']); ?>" required></div>
<div class="form-group"><label>Email</label><input type="email" class="form-control" value="<?php echo sanitize($user['email']); ?>" disabled><p class="form-hint">Email cannot be changed.</p></div>
<div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?php echo sanitize($user['phone']); ?>" required></div>
<div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" value="<?php echo sanitize($user['location']); ?>"></div>
<button type="submit" class="btn btn-primary">Save Changes</button>
</form></div>

<div class="card"><div class="card-header"><h3>🔒 Change Password</h3></div>
<form method="POST"><?php csrfField(); ?><input type="hidden" name="action" value="password">
<div class="form-group"><label>Current Password</label><input type="password" name="current_password" class="form-control" required></div>
<div class="form-group"><label>New Password</label><input type="password" name="new_password" class="form-control" required></div>
<div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
<button type="submit" class="btn btn-primary">Update Password</button>
</form></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
