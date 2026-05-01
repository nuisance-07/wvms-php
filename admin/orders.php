<?php
$pageTitle = 'All Orders';
require_once __DIR__ . '/../includes/header.php';
requireRole('admin');
$db = getDB();

$status=$_GET['status']??''; $vid=(int)($_GET['vendor_id']??0);
$where="WHERE 1=1"; $params=[];
if($status&&in_array($status,['pending','accepted','dispatched','delivered','cancelled'])){$where.=" AND wo.status=?";$params[]=$status;}
if($vid){$where.=" AND wo.vendor_id=?";$params[]=$vid;}

$count=$db->prepare("SELECT COUNT(*) FROM water_orders wo $where"); $count->execute($params);
$page=paginate($count->fetchColumn(),(int)($_GET['page']??1),15);
$stmt=$db->prepare("SELECT wo.*,u.name as customer_name,v.business_name FROM water_orders wo JOIN users u ON wo.customer_id=u.id JOIN vendors v ON wo.vendor_id=v.id $where ORDER BY wo.created_at DESC LIMIT {$page['per_page']} OFFSET {$page['offset']}");
$stmt->execute($params); $orders=$stmt->fetchAll();
$vendors=$db->query("SELECT v.id,v.business_name FROM vendors v")->fetchAll();
?>
<div class="filters">
    <a href="/admin/orders.php" class="btn btn-sm <?php echo !$status?'btn-primary':'btn-outline'; ?>">All</a>
    <?php foreach(['pending','accepted','dispatched','delivered','cancelled'] as $s): ?>
    <a href="?status=<?php echo $s; ?>" class="btn btn-sm <?php echo $status===$s?'btn-primary':'btn-outline'; ?>"><?php echo ucfirst($s); ?></a>
    <?php endforeach; ?>
    <select onchange="location='?vendor_id='+this.value" class="form-control" style="width:auto;padding:6px 12px">
        <option value="">All Vendors</option>
        <?php foreach($vendors as $vn): ?><option value="<?php echo $vn['id']; ?>" <?php echo $vid==$vn['id']?'selected':''; ?>><?php echo sanitize($vn['business_name']); ?></option><?php endforeach; ?>
    </select>
</div>
<div class="table-container">
    <div class="table-header"><h3>📦 System Orders</h3><input type="text" class="table-search" id="os" placeholder="Search..." onkeyup="filterTable('os','ot')"></div>
    <table class="data-table" id="ot"><thead><tr><th>#</th><th>Customer</th><th>Vendor</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
    <?php if(empty($orders)): ?><tr><td colspan="7" class="no-data">No orders found.</td></tr>
    <?php else: foreach($orders as $o): ?>
    <tr><td>#<?php echo $o['id']; ?></td><td><?php echo sanitize($o['customer_name']); ?></td><td><?php echo sanitize($o['business_name']); ?></td><td><?php echo $o['quantity_litres']; ?>L</td><td><?php echo formatCurrency($o['total_amount']); ?></td><td><?php echo getStatusBadge($o['status']); ?></td><td><?php echo formatDate($o['created_at']); ?></td></tr>
    <?php endforeach; endif; ?>
    </tbody></table>
    <?php echo renderPagination($page,'/admin/orders.php'); ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
