# Event Attendance & Souvenir Management System

Production-ready starter application for internal company events.

## Stack
- PHP 8.2+
- MySQL 8.0+
- PDO
- Bootstrap 5
- PHPMailer 6
- Endroid QR Code
- PhpSpreadsheet
- html5-qrcode

## Modules
1. Authentication / RBAC
2. Dashboard
3. Employee Master CRUD
4. Employee CSV/XLSX import
5. Event Master CRUD
6. Event guest selection
7. Bulk invitation
8. QR token generation
9. Email invitation + QR
10. Attendance scanner
11. Souvenir master
12. Event souvenir allocation
13. Souvenir scanner
14. Attendance report
15. Souvenir report
16. CSV export
17. Audit log
18. Login throttling
19. CSRF protection
20. Security headers
21. Health check

## Installation
1. Install PHP 8.2+, MySQL 8+, Composer and Apache.
2. Create database by importing `database/schema.sql`.
3. Copy `.env.example` to `.env`.
4. Set DB and SMTP values.
5. Run `composer install`.
6. Set Apache document root to the `public` directory.
7. Create the first admin:
   `php cli/create_admin.php admin@company.com "System Admin" "ChangeMe!123"`
8. Open `/login`.
9. Change the initial password immediately.
10. Use HTTPS in production.

## Employee import
CSV/XLSX columns:
nik,name,section,department,division,email,status

Status values: ACTIVE / INACTIVE.

## Production deployment
- Do not expose project root; only `public/`.
- Use HTTPS.
- Set APP_DEBUG=false.
- Set a strong APP_KEY.
- Use a dedicated MySQL user with least privileges.
- Configure DB backup.
- Configure SMTP credentials outside source control.
- Put `.env` outside the web root.
- Restrict admin accounts.
- Monitor PHP/Apache/MySQL logs.

## Important QR rule
The QR contains only a random token. NIK, employee name and email are not encoded into the QR.
The token is unique per invitation (Employee + Event).
