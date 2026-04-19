# 🍨 Dairich Ice Cream — B2B Distributor Portal

A full-stack B2B web application for Dairich Ice Cream that manages the complete distributor lifecycle — from enquiry submission and admin approval, to bulk order placement, delivery tracking, invoice generation, and feedback.

---

## 👥 Team

| Name | Role |
|------|------|
| Sayee Wagh | Frontend Development & Database |
| Ojasvi Jaiswal | Backend Development (REST API & Database) |

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, CSS3, Vanilla JavaScript (Fetch API) |
| Backend | PHP 8 (RESTful API) |
| Database | PostgreSQL |
| Server | Apache via XAMPP |
| Auth | Bearer Token (stored in localStorage) |
| Version Control | Git & GitHub |

---

## 📁 Project Structure

```
Dairich-Icecream-Portal/
│
├── index.html                  # Main marketing website with B2B enquiry form
├── style.css                   # Main website stylesheet
├── script.js                   # Main website JavaScript
│
├── ── DISTRIBUTOR PORTAL ──
├── login.html                  # Distributor login
├── register.html               # Distributor registration
├── profile.html                # Distributor dashboard
├── place.html                  # Place bulk order
├── orders_list.html            # Order history
├── detail.html                 # Order detail
├── track.html                  # Delivery tracking
├── invoice.html                # Printable invoice / PDF
├── feedback_submit.html        # Submit feedback
├── feedback_list.html          # Feedback history
├── dist-portal.css             # Shared portal stylesheet
│
├── ── ADMIN PORTAL ──
├── admin_login.html            # Admin login
├── admin_dashboard.html        # Stats dashboard
├── admin_distributors.html     # Approve/manage distributors
├── admin_orders.html           # Manage & update orders
├── admin_enquiries.html        # Review & approve enquiries
├── admin_products.html         # Add/edit/remove products
├── admin_feedback.html         # View all distributor feedback
├── admin-portal.js             # Shared admin JavaScript
│
├── ── BACKEND API ──
├── api/
│   ├── enquiry/submit.php          # Submit B2B enquiry (public)
│   ├── distributor/
│   │   ├── login.php               # Distributor login → returns token
│   │   ├── logout.php              # Invalidate session token
│   │   ├── register.php            # Self-registration
│   │   └── profile.php             # Get profile info
│   ├── orders/
│   │   ├── place.php               # Place a bulk order
│   │   ├── orderslist.php          # List all orders for distributor
│   │   └── detail.php              # Get single order detail
│   ├── delivery/track.php          # Track delivery status
│   ├── products/productlist.php    # Get active products
│   ├── feedback/
│   │   ├── submit.php              # Submit feedback
│   │   └── feedbacklist.php        # List distributor's feedback
│   └── admin/
│       ├── alogin.php              # Admin login
│       ├── alogout.php             # Admin logout
│       ├── dashboard.php           # Dashboard stats
│       ├── distributors/           # Activate/deactivate distributors
│       ├── enquires/               # Approve enquiries, update status
│       ├── order/                  # List & update order status
│       ├── deliveries/             # Update delivery tracking
│       ├── product/                # CRUD for products
│       └── admin_feedback/         # View all feedback
│
├── ── CONFIG ──
├── config/
│   ├── db.php                  # PostgreSQL database connection
│   ├── auth.php                # Distributor Bearer token auth
│   ├── admin_auth.php          # Admin Bearer token auth
│   └── helpers.php             # Shared utility functions
│
└── schema.sql                  # Full PostgreSQL database schema
```

---

## 🗄️ Database Schema

The PostgreSQL database contains the following tables:

| Table | Description |
|-------|-------------|
| `admin_users` | Admin accounts with roles (superadmin / staff) |
| `admin_sessions` | Admin Bearer token sessions |
| `products` | Ice cream products with pricing |
| `enquiries` | B2B enquiries submitted from main website |
| `enquiry_flavours` | Products linked to each enquiry |
| `distributors` | Approved distributor accounts |
| `distributor_sessions` | Distributor Bearer token sessions |
| `orders` | Bulk orders placed by distributors |
| `order_items` | Individual products within each order |
| `delivery` | Delivery tracking per order |
| `feedback` | Distributor feedback on delivered orders |

---

## 🔄 Application Flow

```
1. ENQUIRY
   Visitor fills B2B enquiry form on index.html
   → Saved to enquiries table
   → Admin reviews in admin_enquiries.html

2. APPROVAL
   Admin approves enquiry → creates distributor account with temp password
   OR
   Distributor self-registers → admin activates account

3. DISTRIBUTOR LOGIN
   Distributor logs in → receives Bearer token
   → Token stored in localStorage
   → Sent with every API request as Authorization header

4. ORDER PLACEMENT
   Distributor selects products + quantities
   → Order saved with real prices from products table
   → Delivery record created (status: pending)

5. ADMIN ORDER MANAGEMENT
   Admin confirms order → updates order + delivery status
   → Distributor can track in real time

6. TRACKING & INVOICE
   Distributor tracks delivery timeline
   → On delivery: can view/print invoice as PDF

7. FEEDBACK
   After delivery: distributor submits star rating + message
   → Admin views all feedback in admin_feedback.html
```

---

## ⚙️ Setup & Installation

### Prerequisites
- XAMPP (Apache + PHP 8)
- PostgreSQL + pgAdmin 4
- Git

### Steps

**1. Clone the repository**
```bash
git clone https://github.com/sayeewagh-dotcom/Dairich-Icecream-Portal.git
cd Dairich-Icecream-Portal
```

**2. Copy to XAMPP**
```
Copy all files to: C:\xampp\htdocs\Dairich Ice Cream Portal\
```

**3. Enable PostgreSQL in PHP**

Open `C:\xampp\php\php.ini` and uncomment:
```
extension=pdo_pgsql
extension=pgsql
```
Restart Apache in XAMPP.

**4. Set up the database**

Open pgAdmin 4 → create a database called `dairich` → open Query Tool → run `schema.sql`.

Then run the missing tables SQL:
```sql
CREATE TABLE IF NOT EXISTS admin_sessions (
    id         SERIAL PRIMARY KEY,
    admin_id   INT NOT NULL REFERENCES admin_users(id) ON DELETE CASCADE,
    token      VARCHAR(255) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS distributor_sessions (
    id              SERIAL PRIMARY KEY,
    distributor_id  INT NOT NULL REFERENCES distributors(id) ON DELETE CASCADE,
    token           VARCHAR(255) NOT NULL UNIQUE,
    expires_at      TIMESTAMP NOT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT NOW()
);
```

Create the admin user (password: `password`):
```sql
INSERT INTO admin_users (name, email, password_hash, role)
VALUES ('Admin', 'admin@dairich.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHpwEjFEW', 'superadmin')
ON CONFLICT (email) DO NOTHING;
```

Add pricing to products:
```sql
ALTER TABLE products ADD COLUMN IF NOT EXISTS price_per_box DECIMAL(10,2) NOT NULL DEFAULT 0.00;

UPDATE products SET price_per_box = 
  CASE name
    WHEN 'Kesar Pista'   THEN 450.00
    WHEN 'Black Current' THEN 380.00
    WHEN 'Chappan Bhog'  THEN 500.00
    WHEN 'Badam Roasted' THEN 420.00
    WHEN 'Anjeer Badam'  THEN 400.00
    WHEN 'Choco Chips'   THEN 350.00
    WHEN 'Chocolate'     THEN 320.00
    WHEN 'Strawberry'    THEN 300.00
    WHEN 'Vanilla'       THEN 280.00
    WHEN 'Chocobar'      THEN 360.00
  END;
```

**5. Configure database connection**

Open `config/db.php` and update:
```php
$host     = 'localhost';
$port     = '5432';
$dbname   = 'dairich';
$user     = 'postgres';       // your pgAdmin username
$password = 'your_password';  // your pgAdmin password
```

**6. Run the project**

Open browser and go to:
```
http://localhost/Dairich%20Ice%20Cream%20Portal/index.html
```

---

## 🔐 Default Login Credentials

| Portal | URL | Email | Password |
|--------|-----|-------|----------|
| Admin | `admin_login.html` | admin@dairich.com | password |
| Distributor | `login.html` | (register first) | (your choice) |

---

## ✅ Features

### Main Website
- Responsive B2B marketing landing page
- Animated hero section, product carousel, marquee strip
- B2B enquiry form with full client-side and server-side validation
- Phone number, company name, flavour selection, message fields
- Distributor Login button in navbar

### Distributor Portal
- Secure registration with password strength meter
- Bearer token authentication
- Dashboard with order stats
- Bulk order placement with real-time price calculation
- Order history with status filters
- Delivery tracking timeline
- Printable invoice / PDF generation
- Star rating feedback system

### Admin Portal
- Secure admin login with role-based access
- Dashboard with live stats (revenue, orders, distributors, enquiries)
- Approve or deactivate distributor accounts
- Review enquiries and convert to distributor accounts
- Update order status and delivery tracking
- Full product CRUD (create, edit, deactivate)
- View all distributor feedback with rating filters

---

## 📝 Notes

- `config/db.php` should never be committed with real credentials — add it to `.gitignore` in production
- Bearer tokens expire after 7 days (distributors) and 8 hours (admins)
- Products use soft delete (`is_active = FALSE`) to preserve order history
- Invoice page supports browser Print → Save as PDF

---

*Built as a full-stack academic project — Dairich Ice Cream B2B Portal*