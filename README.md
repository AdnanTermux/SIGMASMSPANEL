# Sigma SMS A2P OTP Panel

A production-ready, multi-tenant PHP/MySQL web application for managing virtual phone numbers, receiving real OTP messages from a live API, tracking profit per SMS, and providing a hierarchical user management system with a REST API.

---

## Features

- **Real OTP ingestion** from `https://tempnum.net/api/public/otps`
- **Multi-tenant hierarchy**: Admin → Manager → Reseller → Sub-Reseller
- **Profit tracking** per SMS per assigned number
- **Beautiful animated UI** — Bootstrap 5, ApexCharts, DataTables, Select2
- **REST API** with token authentication for programmatic OTP retrieval
- **Railway-ready** — runs with `php -S 0.0.0.0:$PORT`

---

## Requirements

- PHP 8.0+ with extensions: `pdo_mysql`, `curl`, `json`, `mbstring`
- MySQL 5.7+ or MariaDB 10.3+

---

## Deploy on Railway

### 1. Create a Railway project

1. Go to [railway.app](https://railway.app) and create a new project
2. Add a **MySQL** plugin to your project
3. Upload or connect this repository

### 2. Set the Start Command

In Railway project settings → **Deploy** → **Start Command**:
```
php -S 0.0.0.0:${PORT:-8080} -t /app/sigma_sms
```

Or if the project root IS `sigma_sms/`:
```
php -S 0.0.0.0:${PORT:-8080}
```

### 3. Install the application

1. Visit your Railway URL: `https://your-app.railway.app/install.php`
2. Fill in the database credentials from Railway's MySQL plugin environment variables:
   - **DB Host**: value of `MYSQLHOST`
   - **DB Name**: value of `MYSQLDATABASE`
   - **DB User**: value of `MYSQLUSER`
   - **DB Password**: value of `MYSQLPASSWORD`
   - **App URL**: your Railway public URL (e.g. `https://your-app.railway.app`)
3. Set your admin username and password
4. Click **Install Now**
5. **Delete `install.php`** after successful installation

### 4. Environment Variables (optional)

You can pre-configure the app by setting these Railway environment variables:
```
DB_HOST=your-mysql-host
DB_NAME=railway
DB_USER=root
DB_PASS=your-password
APP_URL=https://your-app.railway.app
```

---

## Local Development

```bash
# Clone / extract the project
cd sigma_sms

# Start PHP built-in server
php -S localhost:8080

# Visit http://localhost:8080/install.php
```

---

## Quick Install (Manual)

1. Create MySQL database: `sigma_sms_a2p`
2. Import schema: `mysql -u root -p sigma_sms_a2p < schema.sql`
3. Edit `config.php` with your credentials
4. Default login: **admin** / **password** — change immediately!

---

## Directory Structure

```
sigma_sms/
├── ajax/                    # AJAX endpoints (server-side DataTables, actions)
│   ├── cron_fetch.php       # OTP ingestion from external API
│   ├── dashboard_stats.php  # Dashboard statistics
│   ├── dashboard_charts.php # Chart data
│   ├── dt_sms_reports.php   # SMS reports DataTable
│   ├── dt_profit_reports.php
│   ├── dt_numbers.php
│   ├── dt_users.php
│   ├── aj_numbers.php       # Number CRUD actions
│   ├── aj_users.php         # User CRUD actions
│   ├── aj_services.php      # Service autocomplete
│   └── aj_countries.php     # Country list
├── api/
│   └── otps.php             # Public REST API
├── assets/
│   ├── css/app.css          # Custom styles + animations
│   └── js/app.js            # App JavaScript
├── includes/
│   ├── header.php           # Shared HTML head + sidebar + topbar
│   └── footer.php           # Shared scripts + closing tags
├── config.php               # Database + app configuration
├── functions.php            # All helper functions
├── schema.sql               # Database schema
├── install.php              # Web installer (DELETE after use)
├── index.php                # Redirect to dashboard
├── login.php                # Login page
├── logout.php
├── dashboard.php            # Main dashboard
├── sms_reports.php          # SMS reports with filters
├── profit_stats.php         # Profit breakdown
├── numbers.php              # Number management (admin/manager)
├── my_numbers.php           # Assigned numbers (reseller)
├── users.php                # User management
├── profile.php              # Profile + API token
├── notifications.php
├── news_master.php          # Announcements (admin/manager)
├── credit_notes.php
├── payment_requests.php
├── bank_accounts.php
└── statements.php
```

---

## User Roles

| Role           | Capabilities |
|----------------|-------------|
| `admin`        | Full system control, all users, all numbers, all reports |
| `manager`      | Manage own resellers, own numbers, trigger OTP fetch |
| `reseller`     | View assigned numbers, create sub-resellers, assign numbers |
| `sub_reseller` | View only assigned numbers and own profit |

---

## OTP Fetching

OTPs are fetched from the live endpoint:
```
GET https://tempnum.net/api/public/otps
```

- **Manual**: Click "Fetch OTPs Now" on the dashboard (admin/manager)
- **Cron**: `* * * * * php /path/to/sigma_sms/ajax/cron_fetch.php`
- Minimum **60-second** interval enforced between fetches

---

## REST API

```
GET /api/otps.php?token=YOUR_TOKEN
```

**Parameters:**
| Param    | Description                        |
|----------|------------------------------------|
| `token`  | Your API token (required)          |
| `from`   | Start date YYYY-MM-DD              |
| `to`     | End date YYYY-MM-DD                |
| `service`| Filter by service (e.g. `viber`)   |
| `country`| Filter by country code (e.g. `MM`) |
| `number` | Filter by phone number             |
| `page`   | Page number (default: 1)           |
| `limit`  | Records per page (max: 500)        |

**Example response:**
```json
{
  "status": "success",
  "total": 42,
  "page": 1,
  "limit": 100,
  "total_pages": 1,
  "data": [
    {
      "number": "+959661902830",
      "service": "viber",
      "country": "MM",
      "otp": "685102",
      "message": "Your viber verification code is: 685102",
      "received_at": "2026-04-27 12:36:57",
      "rate": "0.005500",
      "profit": "0.005500"
    }
  ]
}
```

Generate your API token at: **Profile & API Token** page.

---

## Security

- All SQL queries use **PDO prepared statements** — no SQL injection
- Passwords hashed with `password_hash()` (bcrypt)
- CSRF token protection on all forms
- Session-based auth with role checks on every page
- API tokens: 64-char cryptographically random hex strings
- **Change the default admin password immediately after install**
- **Delete `install.php` after installation**
- Use HTTPS in production

---

## Default Credentials

| Username | Password   |
|----------|------------|
| `admin`  | `password` |

⚠️ **Change immediately after first login.**

---

## License

MIT — Free to use and modify.
