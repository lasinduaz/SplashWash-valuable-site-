# SplashWash - Vulnerable PHP Lab

> ⚠️ **Important:** This project is intentionally vulnerable and is meant for local learning only. It is **safe to use in a private lab environment**, but it may still contain bugs, insecure code paths, and incomplete features because it is still under development.

## What this project is

SplashWash is a PHP + MySQL training app for learning web security concepts such as SQL injection, XSS, CSRF, and access control. It is designed for hands-on practice, testing, and write-ups in an isolated environment.

## Current status

- Still under development
- Some features may be incomplete or buggy
- Security weaknesses are intentionally present for learning purposes
- Use only on your own machine, VM, or isolated lab network

## Fork this project

You are free to fork this project and build whatever you want on top of it for your own learning. If you do, please keep it private or use it only in a safe lab environment unless you have cleaned up the insecure parts.

## Supported platforms

You can run this app on:

- macOS
- Windows
- Linux

The easiest option is to use a local web stack such as XAMPP, or install Apache, PHP, and MySQL/MariaDB manually.

## Requirements

- PHP 8.2 or later
- MySQL or MariaDB
- Apache or another PHP-capable web server
- Composer, if you want to run tests

## Installation guide

### Option 1: XAMPP or similar local stack

1. Install XAMPP on macOS, Windows, or Linux.
2. Start Apache and MySQL from the control panel.
3. Copy this repository into the web root:
   - macOS: `/Applications/XAMPP/htdocs/`
   - Windows: `C:\xampp\htdocs\`
   - Linux: `/opt/lampp/htdocs/` or your local Apache web root
4. Import the database:
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create the database expected by the app
   - Import the SQL file from the project folder
5. Update database settings in [application/php/config/db_connection.php](application/php/config/db_connection.php) if needed.
6. Open the app in your browser using the local URL for the folder.

### Option 2: Manual Apache + PHP + MySQL setup

1. Install Apache, PHP, and MySQL/MariaDB using your operating system package manager.
2. Enable the PHP MySQL extension.
3. Place the project in your server document root.
4. Import the database schema.
5. Confirm the credentials in [application/php/config/db_connection.php](application/php/config/db_connection.php).
6. Load the app from `localhost`.

## Platform notes

### macOS

- XAMPP is the fastest path for local testing.
- If you use Homebrew, install Apache, PHP, and MariaDB separately.
- Make sure the database service is running before opening the app.

### Windows

- XAMPP or WAMP works well for this project.
- Place the project under `htdocs` or the equivalent web root.
- Run your terminal or editor as needed if file permissions cause issues.

### Linux

- You can use XAMPP, LAMP, or Docker.
- If you install packages manually, ensure Apache can read the project files.
- Use the correct web root for your distribution.

## Configuration

The default database connection file is [application/php/config/db_connection.php](application/php/config/db_connection.php). It uses local-style defaults such as `root`, but you should change them if your environment is different.

## Known issues by design

This training app intentionally includes security weaknesses and unsafe patterns so you can study them in a lab:

- SQL injection
- Cross-site scripting
- CSRF risks
- Plaintext password storage
- Broken access control
- Weak/default credentials for local setup
- Inconsistent output handling in some pages

## Safe usage reminder

This project is intended for offline learning and local testing only. Do not deploy it on a public server or expose it to the internet.

## Learning goals

- Learn how common web vulnerabilities work
- Practice safe testing in a local environment
- Study how to fix issues with prepared statements, output encoding, CSRF tokens, password hashing, and access control

## Suggested workflow

1. Fork the project.
2. Run it locally on macOS, Windows, or Linux.
3. Explore the Learn page and the vulnerable flows.
4. Document the bugs you find.
5. Patch the code in a separate branch if you want to harden it.

## License

This project is currently intended for personal and educational use only unless a separate license is added later.