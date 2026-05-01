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
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="public-body">
<div class="public-wrapper">
    <div class="landing-hero">
        <div class="hero-content">
            <div style="font-size:4rem;margin-bottom:16px">💧</div>
            <h1>Water Vendor Management System</h1>
            <p>Digitizing water vendor operations in urban Kenya. Order water, track deliveries, and manage your business — all in one platform.</p>
            <div class="hero-buttons">
                <a href="/register.php" class="btn btn-white btn-lg">Get Started</a>
                <a href="/login.php" class="btn btn-outline btn-lg" style="border-color:#fff;color:#fff">Login</a>
            </div>
        </div>
        <div class="hero-wave">
            <svg viewBox="0 0 1440 80" preserveAspectRatio="none">
                <path fill="#F0F4F8" d="M0,40 C360,80 720,0 1440,40 L1440,80 L0,80 Z"/>
            </svg>
        </div>
    </div>

    <section class="features-section">
        <h2>Why Choose WVMS?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="icon">🛒</div>
                <h3>Easy Ordering</h3>
                <p>Place water orders in seconds. Select your quantity, delivery address, and preferred time.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📍</div>
                <h3>Track Deliveries</h3>
                <p>Real-time order tracking with status updates from placement to delivery.</p>
            </div>
            <div class="feature-card">
                <div class="icon">💳</div>
                <h3>Flexible Payments</h3>
                <p>Pay via Cash or M-Pesa. All transactions recorded and verified.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📊</div>
                <h3>Business Insights</h3>
                <p>Vendors get detailed reports on sales, revenue, and delivery performance.</p>
            </div>
            <div class="feature-card">
                <div class="icon">⭐</div>
                <h3>Ratings & Feedback</h3>
                <p>Rate your delivery experience and help vendors improve their service.</p>
            </div>
            <div class="feature-card">
                <div class="icon">🔔</div>
                <h3>Instant Notifications</h3>
                <p>Stay updated with real-time alerts on order status and payments.</p>
            </div>
        </div>
    </section>

    <footer style="text-align:center;padding:32px;color:var(--text-light);font-size:0.9rem;border-top:1px solid var(--border)">
        <p>© 2026 WVMS — Water Vendor Management System. All rights reserved.</p>
    </footer>
</div>
</body>
</html>
