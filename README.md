<div align="center">

# 🐾 PawsHome

### A Full-Stack Animal Adoption Web Application

[![Live Demo](https://img.shields.io/badge/Live%20Demo-pawshomes.infinityfree.io-orange?style=for-the-badge&logo=google-chrome&logoColor=white)](https://pawshomes.infinityfree.io/)
[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-CDN-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)

*Connecting loving families with animals in need. Every pet deserves a warm home.*

</div>

---

## 📌 Overview

**PawsHome** is a full-stack web application that streamlines the animal adoption process. Users can browse available pets, submit adoption requests, and track their application status — while administrators manage the entire workflow from a dedicated dashboard.

Built with pure PHP and MySQL (no frameworks), deployed live on InfinityFree free hosting.

---

## 🌐 Live Demo

🔗 **[https://pawshomes.infinityfree.io/](https://pawshomes.infinityfree.io/)**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@pawshome.com | *(contact owner)* |
| User | Register freely | — |

---

## ✨ Features

### 👤 User Side
- 📋 Register & login with secure bcrypt password hashing
- 🔍 Browse pets with search & filter by **type**, **gender**, **status**
- 🐶 View detailed pet profiles with photos and description
- ❤️ Submit adoption requests in one click
- 📊 Personal dashboard — track all requests with live status (Pending / Accepted / Rejected)
- 🐾 List your own pet for adoption with photo upload
- 🌙 Dark mode with localStorage persistence

### 🛡️ Admin Side
- 📈 Dashboard with stats — total users, pets, available, adopted
- ✅ Accept or reject adoption requests
- 🔄 Auto-updates pet status to **Adopted** on acceptance
- ❌ Auto-rejects all other pending requests for the same pet
- 🔎 Filter requests by status (Pending / Accepted / Rejected)

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.x (procedural) |
| Database | MySQL with prepared statements (MySQLi) |
| Frontend | Tailwind CSS (CDN), Vanilla JavaScript |
| Icons | Font Awesome 6.5 |
| Alerts | SweetAlert2 |
| Security | bcrypt hashing, CSRF tokens, session hardening |
| Server | Apache, .htaccess |
| Hosting | InfinityFree (free PHP/MySQL hosting) |
| Dev Environment | XAMPP (local) |

---

## 📁 Project Structure

```
pawshome/
├── index.php                  # Homepage — browse & filter pets
├── config/
│   └── db.php                 # DB connection, session bootstrap, CSRF helpers
├── auth/
│   ├── login.php              # Login (User & Admin role toggle)
│   ├── register.php           # User registration
│   └── logout.php             # Session destroy
├── user/
│   ├── dashboard.php          # User dashboard with stats & recent requests
│   ├── add_pet.php            # List a new pet with photo upload
│   ├── adopt.php              # Submit adoption request
│   ├── pet_details.php        # Full pet profile page
│   └── my_requests.php        # Track all adoption requests
├── admin/
│   ├── dashboard.php          # Admin panel — manage all requests
│   ├── manage_requests.php    # Accept/reject request handler
│   └── logout.php
├── includes/
│   ├── header.php             # Navbar, Tailwind config, dark mode
│   └── footer.php             # Footer with quick links & contact
├── uploads/                   # Pet photo uploads
│   └── .htaccess              # Blocks PHP execution in uploads
└── tmp/
    └── sessions/              # Custom session path (InfinityFree compatible)
```

---

## 🗄️ Database Schema

```sql
-- Users
CREATE TABLE Users (
    UserID   INT AUTO_INCREMENT PRIMARY KEY,
    UserName VARCHAR(100) NOT NULL,
    Email    VARCHAR(150) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL
);

-- Admin
CREATE TABLE Admin (
    AdminID   INT AUTO_INCREMENT PRIMARY KEY,
    AdminName VARCHAR(100) NOT NULL,
    Email     VARCHAR(150) NOT NULL UNIQUE,
    Password  VARCHAR(255) NOT NULL
);

-- Animal
CREATE TABLE Animal (
    AnimalID    INT AUTO_INCREMENT PRIMARY KEY,
    Name        VARCHAR(100) NOT NULL,
    Type        VARCHAR(50),
    Gender      VARCHAR(10),
    Age         INT,
    Description TEXT,
    Status      ENUM('Available','Pending','Adopted') DEFAULT 'Available',
    UserID      INT,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);

-- Photo
CREATE TABLE Photo (
    PhotoID  INT AUTO_INCREMENT PRIMARY KEY,
    AnimalID INT NOT NULL,
    PhotoURL VARCHAR(255),
    FOREIGN KEY (AnimalID) REFERENCES Animal(AnimalID)
);

-- Adoption Request
CREATE TABLE Adoption_Request (
    RequestID   INT AUTO_INCREMENT PRIMARY KEY,
    AnimalID    INT NOT NULL,
    UserID      INT NOT NULL,
    RequestDate DATE,
    Status      ENUM('Pending','Accepted','Rejected') DEFAULT 'Pending',
    FOREIGN KEY (AnimalID) REFERENCES Animal(AnimalID),
    FOREIGN KEY (UserID)   REFERENCES Users(UserID)
);
```

---

## 🚀 Local Setup (XAMPP)

```bash
# 1. Clone the repo
git clone https://github.com/jobayer0910/pawshome.git

# 2. Move to XAMPP htdocs
mv pawshome /xampp/htdocs/pawshome

# 3. Import the database
# Open phpMyAdmin → create database 'pawshome' → import pawshome.sql

# 4. Configure db.php
# Open config/db.php and set:
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pawshome');

# 5. Visit
http://localhost/pawshome/
```

---

## ☁️ InfinityFree Deployment Notes

> Key differences from localhost that must be handled for free shared hosting:

- `DB_HOST` is **not** `localhost` — use the hostname from cPanel MySQL Details
- `session_save_path` must be overridden to a writable folder inside `htdocs/` (e.g. `tmp/sessions/`) because the default `/php_sessions` path is permission-denied
- All redirects use **absolute root-relative paths** (`/auth/login.php`) — never relative (`../auth/login.php`)
- `display_errors` should be **Off** in production; use `error_log()` instead
- `uploads/.htaccess` blocks direct PHP execution inside the uploads directory

---

## 🔐 Security Features

- ✅ **bcrypt** password hashing via `password_hash()` / `password_verify()`
- ✅ **Prepared statements** on every database query — no raw SQL interpolation
- ✅ **CSRF token** generation and verification on all state-changing forms
- ✅ **Session regeneration** on login to prevent session fixation
- ✅ **Role-based access control** — user routes and admin routes are independently guarded
- ✅ **`htmlspecialchars()`** on all user-supplied output
- ✅ **File upload validation** — MIME type check, 5 MB size cap, PHP execution blocked in uploads

---

## 👨‍💻 Author

**Atik Shahrier Rakir**
- 🎓 BSc in Computer Science — AIUB (American International University–Bangladesh)
- 📧 atikshahrier@gmail.com
- 🔗 [LinkedIn](https://linkedin.com/in/) | [GitHub](https://github.com/jobayer0910)
