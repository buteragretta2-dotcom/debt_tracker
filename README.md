# Small Business Debt and Payment Tracker (PHP + MySQL)

One language for the whole system: **PHP**. PHP generates the pages (HTML/CSS are only the output format), talks to MySQL through PDO, and also performs the backup. The only other text is the SQL in `schema.sql`, which creates the database.

## Setup (Windows, XAMPP)
1. Install XAMPP and start **Apache** and **MySQL** in the control panel.
2. Copy the `debt_tracker` folder into `C:\xampp\htdocs\`.
3. Open http://localhost/phpmyadmin, click **Import**, choose `schema.sql`, click **Go**.
4. Open `config.php` and set DB_USER / DB_PASS (XAMPP default: user `root`, empty password) and BUSINESS_NAME.
5. Open http://localhost/debt_tracker/ and log in with **admin / admin123**. Change the password at once (Users page).

## How to learn the system: read in this order
| Step | File | What to learn |
|---|---|---|
| 1 | `schema.sql` | Tables, primary/foreign keys, the `v_debts` view that calculates balances |
| 2 | `config.php` | Database connection (PDO), sessions, timeout |
| 3 | `includes/functions.php` | All business rules: credit limit, record payment (transaction), overdue, validation, CSRF |
| 4 | `login.php`, `logout.php` | Password hashing and verification, session handling |
| 5 | `includes/header.php`, `footer.php` | Shared layout, menu, access check on every page |
| 6 | `customers.php`, `customer_form.php`, `customer_view.php` | Search, add/edit, statement |
| 7 | `debt_form.php`, `payment_form.php`, `receipt.php` | Recording debts and payments, receipts |
| 8 | `index.php`, `reminders.php`, `reports.php` | Dashboard, reminders log, four reports |
| 9 | `users.php`, `backup.php` | Administrator functions |

## Try it (test data walk-through)
1. Add a customer with credit limit 100000. 2. Record a debt of 60000 due in the past: it shows as Overdue on the dashboard. 3. Try a second debt of 60000: it is refused (credit limit). 4. Record a payment of 20000: balance 40000, receipt printed. 5. Try to pay 999999: refused. 6. Pay 40000: status Paid. 7. Open Reports and Backup.

## Security features (match Chapter Five)
Password hashing, prepared statements everywhere, output escaping with `e()`, CSRF token on every form, role checks, session timeout, database transaction for payments, and no deletion of debts or payments.

## Before real use
Change the admin password, set a real BUSINESS_NAME, use HTTPS if hosted online, and take regular backups.
