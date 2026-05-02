<?php
$pageTitle = 'User Management';
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    checkCSRF();
    $action = $_POST['action']??'';
    if ($action==='toggle_status') {
        $uid=(int)$_POST['user_id']; $new=$_POST['new_status'];
        if(in_array($new,['active','inactive'])) { $db->prepare("UPDATE users SET status=? WHERE id=? AND id!=?")->execute([$new,$uid,$_SESSION['user_id']]); setFlash('success','User status updated.'); }
    } elseif ($action==='reset_password') {
        $uid=(int)$_POST['user_id'];
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash('Password@123',PASSWORD_BCRYPT),$uid]);
        setFlash('success','Password reset to Password@123');
    } elseif ($action==='add_vendor') {
        $name=sanitize($_POST['name']??''); $email=sanitize($_POST['email']??''); $phone=sanitize($_POST['phone']??'');
        $biz=sanitize($_POST['business_name']??''); $area=sanitize($_POST['service_area']??''); $location=sanitize($_POST['location']??'');
        $chk=$db->prepare("SELECT id FROM users WHERE email=?"); $chk->execute([$email]);
        if($chk->fetch()) { setFlash('error','Email already exists.'); }
        else {
            $db->prepare("INSERT INTO users (name,email,phone,password,role,location,status) VALUES (?,?,?,?,'vendor',?,'active')")
                ->execute([$name,$email,$phone,password_hash('Vendor@2026',PASSWORD_BCRYPT),$location]);
            $userId=$db->lastInsertId();
            $db->prepare("INSERT INTO vendors (user_id,business_name,service_area,status) VALUES (?,?,?,'active')")->execute([$userId,$biz,$area]);
            setFlash('success','Vendor added! Default password: Vendor@2026');
        }
    }
    redirect('/admin/users.php');
}

$role = $_GET['role']??'';
$where = $role && in_array($role,['customer','vendor','admin']) ? "WHERE role='$role'" : "WHERE 1=1";
$users = $db->query("SELECT * FROM users $where ORDER BY created_at DESC")->fetchAll();
?>

<div class="filters">
    <a href="/admin/users.php" class="btn btn-sm <?php echo !$role?'btn-primary':'btn-outline'; ?>">All</a>
    <a href="?role=customer" class="btn btn-sm <?php echo $role==='customer'?'btn-primary':'btn-outline'; ?>">Customers</a>
    <a href="?role=vendor" class="btn btn-sm <?php echo $role==='vendor'?'btn-primary':'btn-outline'; ?>">Vendors</a>
    <a href="?role=admin" class="btn btn-sm <?php echo $role==='admin'?'btn-primary':'btn-outline'; ?>">Admins</a>
    <button class="btn btn-success btn-sm" onclick="document.getElementById('addVendorModal').classList.add('show')" style="margin-left:auto">+ Add Vendor</button>
</div>

<div class="table-wrapper fade-in">
    <div class="table-header-row">
        <div class="table-title">👥 Manage Users</div>
    </div>
    <table class="data-table">
        <thead>
            <tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th>Action</th></tr>
        </thead>
        <tbody>
        <?php foreach($users as $u): ?>
        <tr>
            <td><strong><?php echo sanitize($u['name']); ?></strong></td>
            <td><?php echo sanitize($u['email']); ?></td>
            <td><?php echo sanitize($u['phone']); ?></td>
            <td><span class="badge <?php echo $u['role']==='admin'?'badge-error':($u['role']==='vendor'?'badge-warning':'badge-info'); ?>"><?php echo strtoupper($u['role']); ?></span></td>
            <td>
                <?php if($u['status']==='active'): ?><span class="badge badge-success">Active</span>
                <?php else: ?><span class="badge badge-error">Inactive</span><?php endif; ?>
            </td>
            <td><?php echo formatDate($u['created_at']); ?></td>
            <td>
                <?php if($u['id'] !== $_SESSION['user_id']): ?>
                <form method="POST" style="display:inline"><?php csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="<?php echo $u['status']==='active'?'deactivate':'activate'; ?>"><button class="btn btn-sm <?php echo $u['status']==='active'?'btn-danger':'btn-success'; ?>"><?php echo $u['status']==='active'?'Deactivate':'Activate'; ?></button></form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Add Vendor Modal -->
<div class="modal-overlay" id="addVendorModal">
<div class="modal"><h3>🏪 Add New Vendor</h3>
<form method="POST"><?php csrfField(); ?><input type="hidden" name="action" value="add_vendor">
<div class="form-row"><div class="form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div><div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div></div>
<div class="form-row"><div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" required></div><div class="form-group"><label>Location</label><input type="text" name="location" class="form-control" required></div></div>
<div class="form-group"><label>Business Name</label><input type="text" name="business_name" class="form-control" required></div>
<div class="form-group"><label>Service Area</label><input type="text" name="service_area" class="form-control" required></div>
<p class="form-hint">Default password: Vendor@2026</p>
<div class="modal-actions"><button type="button" class="btn btn-outline" onclick="document.getElementById('addVendorModal').classList.remove('show')">Cancel</button><button type="submit" class="btn btn-primary">Add Vendor</button></div>
</form></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
