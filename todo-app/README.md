# TaskFlow — To-Do List with User Authentication

A production-quality To-Do List web app built with **PHP 8 + MySQL + Vanilla JS**, featuring full authentication, AJAX-powered task management, a live dashboard, dark mode, and a modern glassmorphism UI.

## Tech Stack
- **Frontend:** HTML5, CSS3 (custom, no framework), Vanilla JavaScript
- **Backend:** PHP 8+ (PDO, prepared statements)
- **Database:** MySQL 8+
- **Charts:** Chart.js (CDN)
- **Icons/Fonts:** Font Awesome + Google Fonts "Poppins" (CDN)

## Setup Instructions

### 1. Requirements
- PHP 8.0 or higher, with the `pdo_mysql` and `fileinfo` extensions enabled
- MySQL 8.0+ (or MariaDB 10.4+)
- A local server stack: XAMPP, MAMP, Laragon, or `php -S` + a MySQL install

### 2. Database Setup
```bash
mysql -u root -p < sql/todo.sql
```
This creates the `todo_app` database with the `users`, `tasks`, and `activity_log` tables (foreign keys, indexes, and a FULLTEXT search index included).

### 3. Configure Database Credentials
Edit `config/database.php`, or set environment variables:
```
DB_HOST=127.0.0.1
DB_NAME=todo_app
DB_USER=root
DB_PASS=your_password
```

### 4. File Permissions
Make sure the web server can write to the avatar upload folder:
```bash
chmod -R 755 assets/images/avatars
```

### 5. Run It
**Option A — PHP built-in server (quick local testing):**
```bash
php -S localhost:8000
```
Visit `http://localhost:8000`

**Option B — XAMPP/MAMP/Laragon:** drop the `todo-app` folder into your `htdocs`/`www` directory and visit:
```
http://localhost/todo-app/
```

> **Subfolder-safe by design:** `config/database.php` auto-detects how deep the project sits below your web server's document root and defines a `BASE_URL` constant from it (e.g. `/todo-app` if placed in `htdocs/todo-app`, or empty if placed directly in `htdocs`). Every link, redirect, stylesheet/script tag, and AJAX call in the app uses this constant — so it works correctly whether you keep the `todo-app` folder as-is or flatten its contents into the document root. Nothing to hand-edit.

## Folder Structure
```
todo-app/
├── assets/{css,js,images/avatars,icons}
├── config/database.php          # PDO connection
├── includes/                    # header, navbar, sidebar, footer, functions.php
├── auth/                        # register, login, logout, forgot-password
├── dashboard/index.php          # stats + chart + activity feed
├── tasks/                       # index (list/filter/search/sort/pagination) + AJAX endpoints
├── profile/index.php            # profile info, avatar upload, change password
├── sql/todo.sql                 # database schema
└── index.php                    # entry point / redirect
```

## Security Features Implemented
- Passwords hashed with `password_hash()` (bcrypt), verified with `password_verify()`
- **100% prepared statements** — no raw SQL interpolation anywhere
- CSRF tokens on every state-changing form/AJAX call, verified with `hash_equals()`
- XSS protection via `htmlspecialchars()` on all output (`e()` helper) and `strip_tags()` on input (`clean()` helper)
- Session hardening: `httponly` + `samesite=Lax` cookies, `session_regenerate_id()` on login/register (prevents session fixation)
- "Remember Me" via random 32-byte token stored server-side with expiry — never the password itself
- Generic login error messages (no account enumeration)
- Every task query scoped to `WHERE user_id = ?` — users can never read/edit/delete another user's data, even by guessing IDs
- File upload validation by real MIME type (`finfo`), not just file extension, with a 2MB size cap

## Features Checklist
- ✅ Register / Login / Logout with sessions
- ✅ Add / Edit / Delete / Complete tasks (AJAX, no page reload)
- ✅ Priority (Low/Medium/High), Due Date, Category, Tags
- ✅ Live search, filters (All/Pending/Completed/High Priority), sort (Date/Priority), pagination
- ✅ Dashboard: welcome banner, 4 stat cards, completion chart, today's tasks, recent activity
- ✅ Dark mode toggle (persisted via localStorage)
- ✅ Toast notifications, confirmation modal before delete, empty states
- ✅ Profile page: edit name, upload avatar, change password
- ✅ Forgot password (dummy — UI + flow only, no email sending wired up)
- ✅ Keyboard shortcuts: `/` to search, `n` for new task, `Esc` to close modals
- ✅ Mobile-first responsive design (off-canvas sidebar under 900px)

## What's Intentionally Left as a Stub
- **Forgot Password** is a UI-complete "dummy" flow per the spec — it doesn't send real emails. To make it real: generate `users.reset_token`/`reset_expires`, email a signed link via a mail library (e.g. PHPMailer + SMTP), and build a `reset-password.php` that verifies the token and lets the user set a new password.
- **CSRF token on GET-based filters** isn't needed (GET requests should be idempotent/safe), but all POST/AJAX actions are protected.
