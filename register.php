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
<body class="public-body">
<div class="auth-wrapper fade-in">
    <!-- Left Branded Panel -->
    <div class="auth-panel">
        <div class="auth-panel-content">
            <div class="auth-logo">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"></path>
                </svg>
            </div>
            <h1>WVMS</h1>
            <p>The modern operating system for urban water distribution.</p>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-form-area">
        <div class="auth-card" style="max-width:500px">
            <h2>Create Account</h2>
            <p>Join WVMS to order water easily</p>
            
            <?php if ($error): ?><div class="alert alert-error"><span>✕</span> <?php echo $error; ?></div><?php endif; ?>
            
            <form method="POST" onsubmit="document.getElementById('regBtn').classList.add('btn-loading')">
                <?php csrfField(); ?>
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" value="<?php echo sanitize($old['name']??''); ?>" required>
                </div>
                <div class="grid-2" style="gap:16px; margin-bottom:20px;">
                    <div>
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo sanitize($old['email']??''); ?>" required>
                    </div>
                    <div>
                        <label class="form-label" for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="0712345678" value="<?php echo sanitize($old['phone']??''); ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="location">Location / Estate</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="e.g. Umoja Estate, Nairobi" value="<?php echo sanitize($old['location']??''); ?>" required>
                </div>
                <div class="grid-2" style="gap:16px; margin-bottom:24px;">
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Min 6 chars" required>
                    </div>
                    <div>
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" id="regBtn" class="btn btn-primary btn-block">Create Account</button>
            </form>
            
            <div style="text-align:center; margin-top:32px; font-size:0.875rem; color:var(--text-secondary)">
                Already have an account? <a href="/login.php" style="font-weight:600">Sign in</a>
            </div>
            <div style="text-align:center; margin-top:16px;">
                <a href="/index.php" style="font-size:0.875rem; color:var(--text-muted)">← Back to Home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
