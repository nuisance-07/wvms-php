<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
if (isLoggedIn()) { redirectToDashboard(); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $user = login($email, $password);
        if ($user) {
            redirectToDashboard();
        } else {
            $error = 'Invalid email or password, or account is inactive.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — WVMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
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
        <div class="auth-card">
            <h2>Welcome Back</h2>
            <p>Sign in to your account to continue</p>
            
            <?php if ($error): ?><div class="alert alert-error"><span>✕</span> <?php echo $error; ?></div><?php endif; ?>
            
            <form method="POST" action="" onsubmit="document.getElementById('loginBtn').classList.add('btn-loading')">
                <?php csrfField(); ?>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo sanitize($email ?? ''); ?>" required autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 24px;">
                    <label style="display:flex; align-items:center; gap:8px; font-size:0.875rem; color:var(--text-secondary); cursor:pointer;">
                        <input type="checkbox" style="width:16px;height:16px;accent-color:var(--primary)"> Remember me
                    </label>
                    <a href="#" style="font-size:0.875rem; font-weight:500;">Forgot password?</a>
                </div>

                <button type="submit" id="loginBtn" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div style="text-align:center; margin-top:32px; font-size:0.875rem; color:var(--text-secondary)">
                Don't have an account? <a href="/register.php" style="font-weight:600">Create one</a>
            </div>
            <div style="text-align:center; margin-top:16px;">
                <a href="/index.php" style="font-size:0.875rem; color:var(--text-muted)">← Back to Home</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
