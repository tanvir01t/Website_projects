# MenStyle — Complete Men's Fashion E-Commerce

A full-stack e-commerce system for men's fashion built with **PHP + MySQL + Bootstrap 5**.

---

## 🚀 Quick Setup (XAMPP)

### Step 1 — Install XAMPP
Download from [apachefriends.org](https://www.apachefriends.org) and install.

### Step 2 — Place Project Files
Copy the `menstyle` folder into:
```
C:\xampp\htdocs\menstyle\         (Windows)
/Applications/XAMPP/htdocs/menstyle/  (Mac)
```

### Step 3 — Create Database
1. Start Apache and MySQL in XAMPP Control Panel
2. Open `http://localhost/phpmyadmin`
3. Click **New** → name it `menstyle_db` → click **Create**
4. Click the database → go to **Import** tab
5. Choose `database.sql` from the project root → click **Go**

### Step 4 — Configure Database
Open `config/db.php` and update if needed:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // Your MySQL username
define('DB_PASS', '');          // Your MySQL password (empty by default in XAMPP)
define('DB_NAME', 'menstyle_db');
define('SITE_URL', 'http://localhost/menstyle');
```

### Step 5 — Run the Site
- **Store Front:** http://localhost/menstyle/
- **Admin Panel:** http://localhost/menstyle/admin/login.php

---

## 🔑 Demo Login Credentials

### Customer Accounts
| Email | Password |
|-------|----------|
| rahim@example.com | password |
| karim@example.com | password |
| mahbub@example.com | password |

### Admin Account
| Email | Password |
|-------|----------|
| admin@menstyle.com | password |

> **Note:** The demo password hash in the SQL is for "password". All passwords use `PASSWORD_DEFAULT` bcrypt hashing.

---

## 📁 Folder Structure

```
menstyle/
├── index.php                    # Homepage / Landing Page
├── database.sql                 # Complete database with demo data
├── README.md                    # This file
│
├── config/
│   └── db.php                   # Database & site configuration
│
├── includes/
│   ├── functions.php            # All helper functions
│   ├── header.php               # Site header / navbar
│   └── footer.php               # Site footer
│
├── pages/
│   ├── shop.php                 # Product listing with filters
│   ├── product.php              # Product detail page
│   ├── cart.php                 # Shopping cart
│   ├── cart-action.php          # Cart AJAX handler
│   ├── checkout.php             # Checkout page
│   ├── order-success.php        # Order confirmation
│   ├── orders.php               # Order history
│   ├── profile.php              # User profile
│   ├── login.php                # User login
│   ├── register.php             # User registration
│   ├── logout.php               # Logout
│   ├── apply-coupon.php         # Coupon AJAX handler
│   └── submit-review.php        # Review submission
│
├── admin/
│   ├── login.php                # Admin login
│   ├── dashboard.php            # Admin dashboard
│   ├── products.php             # List/manage products
│   ├── add-product.php          # Add new product
│   ├── edit-product.php         # Edit existing product
│   ├── orders.php               # Order management
│   ├── users.php                # User management
│   ├── categories.php           # Category management
│   ├── reviews.php              # Review moderation
│   ├── coupons.php              # Coupon management
│   ├── header.php               # Admin layout header
│   ├── footer.php               # Admin layout footer
│   └── logout.php               # Admin logout
│
└── assets/
    ├── css/
    │   └── style.css            # Main stylesheet
    ├── js/
    │   └── main.js              # Main JavaScript
    └── images/
        ├── products/            # Product images (upload via admin)
        ├── categories/          # Category images
        └── placeholder.jpg      # Default placeholder
```

---

## 📦 Database Tables

| Table | Description |
|-------|-------------|
| `users` | Customer accounts |
| `admin` | Admin accounts |
| `categories` | Product categories |
| `products` | All products with pricing |
| `orders` | Customer orders |
| `order_items` | Individual order line items |
| `reviews` | Product reviews/ratings |
| `coupons` | Discount coupon codes |
| `cart` | DB-backed cart (optional) |

---

## ✨ Features Implemented

### Customer Side
- ✅ User registration with validation
- ✅ Secure login (bcrypt password hashing)
- ✅ User profile with editable details & password change
- ✅ Product browsing with categories, search, pagination
- ✅ Product detail page with size/color selection, gallery, reviews
- ✅ Add to cart (AJAX, no page reload)
- ✅ Cart management (update qty, remove items)
- ✅ Coupon code system (WELCOME20, FLAT200, STYLE10)
- ✅ Free shipping on orders over ৳2,000
- ✅ Checkout with delivery address
- ✅ Multiple payment methods (COD, bKash, Nagad, Card)
- ✅ Order history with detailed view
- ✅ Product reviews and ratings

### Landing Page
- ✅ Hero section with CTA buttons
- ✅ Features strip (free delivery, authentic, returns, support)
- ✅ Category grid
- ✅ Featured products
- ✅ Promotional banner
- ✅ Customer testimonials
- ✅ FAQ accordion
- ✅ Contact section
- ✅ Fully responsive (mobile-first)

### Admin Panel
- ✅ Secure admin login
- ✅ Dashboard with stats (orders, revenue, users, low stock)
- ✅ Product management (add, edit, delete, toggle featured)
- ✅ Image upload for products
- ✅ Order management with status updates
- ✅ User management (block/unblock)
- ✅ Category management
- ✅ Review moderation (approve/reject)
- ✅ Coupon creation and management

---

## 🎨 Design System

- **Display Font:** Cormorant Garamond (luxury serif)
- **Body Font:** DM Sans (modern sans-serif)
- **Primary Color:** `#0d0d0d` (deep black)
- **Accent Color:** `#b89a5a` (burnished gold)
- **Background:** `#f8f5f0` (warm ivory)
- **Theme:** Luxury Editorial Men's Fashion

---

## 🔒 Security Features

- Prepared statements (PDO) — SQL injection prevention
- `password_hash()` / `password_verify()` — bcrypt hashing
- `htmlspecialchars()` — XSS prevention
- Session-based authentication
- Admin and user sessions separated
- Input validation on all forms

---

## 💳 Demo Coupon Codes

| Code | Discount | Min Order |
|------|----------|-----------|
| `WELCOME20` | 20% off | ৳1,000 |
| `FLAT200` | ৳200 off | ৳2,000 |
| `STYLE10` | 10% off | ৳500 |

---

## 📸 Adding Product Images

1. Go to **Admin Panel → Add Product**
2. Upload product image (JPG/PNG/WEBP, max 5MB)
3. Recommended size: **600×800px** (3:4 ratio)
4. Images are saved to `assets/images/products/`
5. Category images go to `assets/images/categories/`

---

## 🔧 Customization

### Change Site Name / Currency
Edit `config/db.php`:
```php
define('SITE_NAME', 'MenStyle');
define('CURRENCY', '৳');
define('SHIPPING_COST', 60);
```

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
  --gold: #b89a5a;    /* Accent color */
  --black: #0d0d0d;   /* Primary dark */
  --ivory: #f8f5f0;   /* Light background */
}
```

---

## 📱 Browser Support
Chrome, Firefox, Safari, Edge — all modern browsers.
Mobile responsive down to 320px width.
