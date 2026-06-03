<!-- /README.md -->

# Vulnerable Car-Wash Clone (Educational)

**Purpose:** intentionally-vulnerable web application to practice defensive and offensive security in a closed lab environment (university assignment).

**Warning:** Do not deploy this on a public or production network. Run inside an isolated VM (e.g., Kali / Ubuntu in VirtualBox/VMware) with no access to production networks.

## Quick setup (local LAMP)
1. Install Apache + PHP + MySQL (e.g., `apache2`, `php`, `php-mysql`, `mysql-server`).
2. Place the `car-wash-clone` folder in your webroot (e.g., `/var/www/html/car-wash-clone`).
3. Create the database:
   - `mysql -u root -p < car_wash_db.sql`
4. Update DB credentials in `/application/php/config/db_connection.php`.
5. Ensure file permissions and that Apache user can read files.
6. Visit `http://localhost/car-wash-clone/index.html`.

## Notes
- The app deliberately includes SQL Injection (raw queries), stored/reflective XSS (unsanitized output), CSRF (no tokens), plaintext password storage, and broken access control.
- Intended for offline testing with tools like OWASP ZAP, Burp Suite, or sqlmap **in a lab only**.
- Do not use exploit payloads elsewhere. Keep testing in an isolated VM.

## Learning goals
- Identify OWASP Top 10 vulnerabilities.
- Practice safe exploitation in a controlled environment.
- Learn mitigations (prepared statements, output encoding, CSRF tokens, password hashing, RBAC).
