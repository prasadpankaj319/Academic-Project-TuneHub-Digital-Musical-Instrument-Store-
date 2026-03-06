# TuneHub: Digital Musical Instrument Store
**Academic Final Year Project**

TuneHub is a comprehensive, production-ready e-commerce web application built for musicians to explore, purchase, and learn about musical instruments. It features a custom-built secure backend, an elegant glassmorphism frontend, and robust admin capabilities.

## 📁 System Architecture & Directory Structure
This project is built using a strict, clean, and modular architecture without the bloat of heavy package managers.

```text
Final Year Project/
├── assets/         # Static Frontend Resources
│   ├── css/        # Custom styling (style.css, motion.css)
│   ├── js/         # Vanilla JavaScript (animations.js, main.js)
│   └── images/     # Product images and assets
├── config/         # System Configurations
│   ├── database.php# PDO Database connection strings
│   └── mail.php    # SMTP Configuration for PHPMailer
├── db/             # Database Definitions
│   └── schema.sql  # The master SQL scaffolding file for all tables
├── includes/       # Reusable Partials & Core Classes
│   ├── auth.php    # Session initialization and security headers
│   ├── functions.php # Core utility functions (CSRF, sanitization)
│   ├── header.php/footer.php # Global UI layouts
│   └── PHPMailer/  # Core mailing library (Standalone, no Composer)
├── modules/        # Feature-Based Functional Logic
│   ├── admin/      # Secure Administrator Dashboard & Management
│   ├── cart/       # Session-based Shopping Cart & Promo Codes
│   ├── orders/     # Secure Checkout & PDF Invoicing
│   ├── payment/    # Payment Processing & Database Transactions
│   ├── products/   # Catalog Display, Search, and Reviews
│   ├── tutorials/  # Educational Video Integration
│   └── user/       # Auth, Profiling, and Recommendation Engine
├── index.php       # Main Landing Page
├── about.php       # Mission & Company Details
├── contact.php     # Lead Generation & Support Forms
└── faq.php         # Customer Support Information
```

## 🚀 How to Run the Project (Setup Guide)

1. **Environment Setup:** Ensure XAMPP (or equivalent AMP stack) is installed.
2. **Move Files:** Place the `Final Year Project` folder inside your `htdocs` directory.
   - *Path:* `C:\xampp\htdocs\Final Year Project`
3. **Database Scaffolding:**
   - Open phpMyAdmin (`http://localhost/phpmyadmin`).
   - Create a database (the script will actually create `tunehub` automatically if you run it directly).
   - Import the `db/schema.sql` file to build the tables and inject the mock data.
4. **Email Configuration (Optional but Recommended):**
   - Open `config/mail.php`.
   - Update `MAIL_USERNAME` and `MAIL_PASSWORD` with a valid Gmail address and a 16-digit Google App Password for the "Forgot Password" feature to work.
5. **Launch:** Open your browser and navigate to `http://localhost/Final Year Project/index.php`.

## 🔐 Default Test Accounts
The `schema.sql` file seeds the following mock accounts for testing:

**Administrator Account**
- **Username:** `admin`
- **Password:** `admin123`
- *Access to manage products, tutorials, view sales, process orders, and handle user queries.*

**Customer Account**
- **Username:** `johndoe`
- **Password:** `customer123`
- *Access to shopping cart, personalized recommendations, order history, and wishlist.*

## 🛡️ Core Security Features Implemented
During your academic viva/presentation, highlight these enterprise-grade security implementations:
1. **CSRF Protection:** Every state-changing form uses a securely generated, session-bound Anti-CSRF token.
2. **SQL Injection Prevention:** 100% of database queries utilize strict **PDO Prepared Statements**.
3. **XSS Prevention:** All user-generated outputs are wrapped in a custom `sanitize_html()` parser before rendering.
4. **Password Hashing:** User passwords utilize advanced `bcrypt` hashing (via `password_hash()`) to prevent rainbow table attacks.
5. **Session Fixation:** Session IDs are securely regenerated upon successful login.
6. **ACID Compliance:** The checkout process uses Database Transactions (`beginTransaction()`, `commit()`, `rollBack()`) and `SELECT ... FOR UPDATE` row-locking to ensure inventory counts never drop below zero in concurrent environments.

## ✨ Advanced Features to Showcase
- **Recommendation Engine:** Analyzes previous purchase history logic to suggest related gear (`modules/user/recommendations.php`).
- **Archive System:** Products and Tutorials are "Soft Deleted" to preserve historical invoice integrity.
- **Dynamic Cart Animations:** Canvas visualizers and GSAP-style positional cart flying logic purely in Vanilla JS/CSS. 
- **Standalone Architecture:** PHPMailer is integrated natively without the bloat of Composer/Vendor directories, perfect for strict academic review.
