<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { redirectToDashboard(); }

$error = ''; $old = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    $old = ['name'=>sanitize($_POST['name']??''),'email'=>sanitize($_POST['email']??''),'phone'=>sanitize($_POST['phone']??''),'location'=>sanitize($_POST['location']??'')];
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (empty($old['name'])||empty($old['email'])||empty($old['phone'])||empty($password)||empty($old['location'])) {
        $error = 'Please fill in all fields.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $result = registerCustomer($old['name'], $old['email'], $old['phone'], $password, $old['location']);
        if ($result['success']) {
            setFlash('success', 'Account created successfully! Welcome to WVMS.');
            redirect('/customer/index.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — WVMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card" style="max-width:500px">
        <div class="auth-logo">💧</div>
        <h2>Create Account</h2>
        <p class="subtitle">Join WVMS to order water easily</p>
        <?php if ($error): ?><div class="alert alert-error"><span>✕</span> <?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <?php csrfField(); ?>
            <div class="form-group"><label for="name">Full Name</label><input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="<?php echo $old['name']??''; ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label for="email">Email</label><input type="email" id="email" name="email" class="form-control" placeholder="you@email.com" value="<?php echo $old['email']??''; ?>" required></div>
                <div class="form-group"><label for="phone">Phone</label><input type="tel" id="phone" name="phone" class="form-control" placeholder="0712345678" value="<?php echo $old['phone']??''; ?>" required></div>
            </div>
            <div class="form-group"><label for="location">Location / Estate</label><input type="text" id="location" name="location" class="form-control" placeholder="e.g. Umoja Estate, Nairobi" value="<?php echo $old['location']??''; ?>" required></div>
            <div class="form-row">
                <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="password" class="form-control" placeholder="Min 6 chars" required></div>
                <div class="form-group"><label for="confirm_password">Confirm</label><input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter" required></div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">Create Account</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.95rem;color:var(--text-light)">Already have an account? <a href="/login.php">Login</a></p>
    </div>
</div>
</body>
</html>
