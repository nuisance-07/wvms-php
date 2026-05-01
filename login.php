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
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">💧</div>
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your WVMS account</p>
        <?php if ($error): ?><div class="alert alert-error"><span>✕</span> <?php echo $error; ?></div><?php endif; ?>
        <form method="POST" action="">
            <?php csrfField(); ?>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com" value="<?php echo sanitize($email ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">Sign In</button>
        </form>
        <p style="text-align:center;margin-top:20px;font-size:0.95rem;color:var(--text-light)">
            Don't have an account? <a href="/register.php">Register here</a>
        </p>
        <p style="text-align:center;margin-top:8px;font-size:0.85rem;color:var(--text-light)">
            <a href="/index.php">← Back to Home</a>
        </p>
    </div>
</div>
</body>
</html>
