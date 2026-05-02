<?php
/**
 * WVMS — Landing Page
 */
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { redirectToDashboard(); }
$pageTitle = 'Welcome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="WVMS - Digitizing water vendor operations in urban Kenya">
    <title>WVMS — Water Vendor Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        .hero {
            background: linear-gradient(135deg, #0f172a 0%, var(--primary-dark) 100%);
            color: white;
            padding: 100px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0; opacity: 0.1; 
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.8s ease-out forwards;
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 24px;
            color: white;
            line-height: 1.2;
        }
        .hero p {
            font-size: 1.25rem;
            color: rgba(255,255,255,0.8);
            margin-bottom: 40px;
        }
        .hero-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
        }
        .btn-white {
            background: white;
            color: var(--primary-dark);
        }
        .btn-white:hover {
            background: var(--surface-2);
        }
        .btn-outline-white {
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }
        .btn-outline-white:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .features {
            padding: 80px 24px;
            background: var(--surface-2);
        }
        .features h2 {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 48px;
            color: var(--text-primary);
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 32px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: var(--surface);
            padding: 32px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-light);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 24px;
        }
        .feature-card h3 {
            margin-bottom: 12px;
            font-size: 1.25rem;
        }
        .feature-card p {
            color: var(--text-secondary);
        }
        
        .navbar {
            position: absolute;
            top: 0; left: 0; right: 0;
            padding: 24px 48px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .hero-buttons { flex-direction: column; }
            .navbar { padding: 24px; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">💧 WVMS</div>
    <div>
        <a href="/login.php" class="btn btn-outline-white" style="padding: 8px 16px;">Log In</a>
    </div>
</div>

<div class="hero">
    <div class="hero-content">
        <h1>Modernize Your Water Distribution</h1>
        <p>The operating system for urban water vendors and customers in Kenya. Order seamlessly, track deliveries in real-time, and manage your operations.</p>
        <div class="hero-buttons">
            <a href="/register.php" class="btn btn-white btn-lg" style="padding: 16px 32px; font-size: 1.1rem">Get Started for Free</a>
        </div>
    </div>
</div>

<section class="features">
    <h2>Why Choose WVMS?</h2>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon">🛒</div>
            <h3>Easy Ordering</h3>
            <p>Place water orders in seconds. Select your quantity, delivery address, and preferred time with a modern multi-step wizard.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📍</div>
            <h3>Real-time Tracking</h3>
            <p>Know exactly where your water is. Track orders from pending to dispatched to delivered instantly.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>M-Pesa Integration</h3>
            <p>Log transactions seamlessly with built-in M-Pesa transaction code tracking and verification.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">📊</div>
            <h3>Business Analytics</h3>
            <p>Vendors get detailed insights on daily revenue, busiest delivery areas, and performance trends.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">⭐</div>
            <h3>Ratings & Feedback</h3>
            <p>Build trust through a transparent rating system. Customers can rate their delivery experience.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">🔔</div>
            <h3>Smart Notifications</h3>
            <p>Stay updated with a real-time notification bell for order status changes and payments.</p>
        </div>
    </div>
</section>

<footer style="background:var(--surface); text-align:center; padding:32px; color:var(--text-secondary); border-top:1px solid var(--border)">
    <p>© <?php echo date('Y'); ?> WVMS — Water Vendor Management System. All rights reserved.</p>
</footer>

</body>
</html>
