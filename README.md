# WVMS — Water Vendor Management System

A full-stack web application for digitizing water vendor operations in urban Kenya.

## 🌊 Overview

WVMS serves three types of users:
- **Customers** — Place water orders, track deliveries, make payments, give feedback
- **Vendors** — Manage orders, deliveries, payments, view sales reports
- **Administrators** — System oversight, user management, vendor performance, reports

## 🛠 Tech Stack

- **Backend:** PHP 8.3
- **Database:** MySQL 8.0
- **Frontend:** HTML5, CSS3 (vanilla), JavaScript (vanilla)
- **Charts:** Chart.js 4.x
- **Fonts:** Inter (Google Fonts)

## 🚀 Quick Start

### Prerequisites
```bash
sudo apt install php php-mysql mysql-server
```

### Database Setup
```bash
mysql -u root -p < database/schema.sql
```

### Run Development Server
```bash
php -S localhost:8000
```

Visit `http://localhost:8000`

## 🔑 Default Login Credentials

| Role     | Email               | Password    |
|----------|---------------------|-------------|
| Admin    | admin@wvms.co.ke    | Admin@2026  |
| Vendor   | vendor@wvms.co.ke   | Admin@2026  |
| Customer | customer@wvms.co.ke | Admin@2026  |

## 📁 Project Structure

```
/wvms
├── admin/          # Admin panel pages
├── vendor/         # Vendor dashboard pages
├── customer/       # Customer portal pages
├── includes/       # Core PHP (db, auth, functions, header, footer)
├── assets/         # CSS, JS, images
├── api/            # AJAX endpoints
├── database/       # SQL schema
├── reports/        # Print-friendly reports
├── index.php       # Landing page
├── login.php       # Authentication
├── register.php    # Customer registration
└── logout.php      # Session destroy
```

## 🔒 Security Features

- bcrypt password hashing
- Session-based auth with role-based access control (RBAC)
- CSRF token protection on all forms
- PDO prepared statements (SQL injection prevention)
- XSS prevention via input sanitization

## 🗄 Database

7 core tables: `users`, `vendors`, `water_orders`, `payments`, `deliveries`, `notifications`, `feedback`

**Triggers:**
- Auto-create delivery record on order placement
- Auto-create notification on order status change
- Auto-notify on payment confirmation

## 🔮 Future Scope

- **GPS Real-Time Delivery Tracking:** Integration with Maps APIs to allow customers and admins to track water delivery vehicles in real-time, improving transparency and reducing support queries.
- **SMS Notifications via Africa's Talking API:** Proactive SMS alerts for order confirmations, delivery dispatch, and payment receipts, ensuring customers without continuous internet access stay informed.
- **Full M-Pesa Daraja API Integration:** Automated, seamless payment processing using Safaricom's Daraja API for STK Push and C2B/B2C transactions, replacing manual payment verification.
- **Multi-Vendor Marketplace Mode:** Expanding the platform to allow independent water vendors to register, manage their own fleets, and compete on the platform, providing customers with more choices based on rating and proximity.

## 📄 License

© 2026 WVMS. All rights reserved.
