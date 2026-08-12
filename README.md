# SplashWash — Vulnerable PHP Lab

[![Status](https://img.shields.io/badge/status-under%20development-yellow)]()
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777bb4)]()
[![Platform](https://img.shields.io/badge/platform-macOS%20%7C%20Windows%20%7C%20Linux-blue)]()
[![License](https://img.shields.io/badge/license-educational--use--only-lightgrey)]()

> ⚠️ **Intentionally vulnerable — for local learning only.**
> This project is safe to run in a private lab environment, but it is **not** production software. It contains deliberate security weaknesses, may have incomplete features, and should never be exposed to the internet or a shared network.

---

## Table of Contents

- [About](#about)
- [Current Status](#current-status)
- [Requirements](#requirements)
- [Installation](#installation)
  - [Option 1: XAMPP or a similar local stack](#option-1-xampp-or-a-similar-local-stack)
  - [Option 2: Manual Apache + PHP + MySQL setup](#option-2-manual-apache--php--mysql-setup)
- [Platform Notes](#platform-notes)
- [Configuration](#configuration)
- [Known Vulnerabilities (By Design)](#known-vulnerabilities-by-design)
- [Learning Goals](#learning-goals)
- [Suggested Workflow](#suggested-workflow)
- [Forking This Project](#forking-this-project)
- [Safe Usage Reminder](#safe-usage-reminder)
- [License](#license)

---

## About

**SplashWash** is a PHP + MySQL training application built to practice hands-on web security concepts — SQL injection, XSS, CSRF, and broken access control — in a safe, local environment. It doubles as a car wash management app on the surface, giving vulnerabilities realistic context (bookings, roles, customer data) rather than isolated toy examples.

It's designed for personal study, testing, and write-ups as part of a cybersecurity learning path — not for production use.

## Current Status

- 🚧 Still under active development
- Some features may be incomplete or buggy
- Security weaknesses are **intentionally present** for learning purposes
- Intended for use only on your own machine, a VM, or an isolated lab network

## Requirements

| Requirement | Version / Notes |
|---|---|
| PHP | 8.2 or later |
| Database | MySQL or MariaDB |
| Web server | Apache (or another PHP-capable server) |
| Composer | Optional — only needed to run tests |

## Installation

### Option 1: XAMPP or a similar local stack

1. Install [XAMPP](https://www.apachefriends.org/) for your OS.
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Copy this repository into your web root:

   | OS | Path |
   |---|---|
   | macOS | `/Applications/XAMPP/htdocs/` |
   | Windows | `C:\xampp\htdocs\` |
   | Linux | `/opt/lampp/htdocs/` (or your local Apache web root) |

4. Import the database:
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create the database expected by the app
   - Import the provided `.sql` file from the project folder
5. Update credentials in [`application/php/config/db_connection.php`](application/php/config/db_connection.php) if your local setup differs from the defaults.
6. Open the app in your browser at the local URL for the project folder.

### Option 2: Manual Apache + PHP + MySQL setup

1. Install Apache, PHP, and MySQL/MariaDB via your OS package manager.
2. Enable the PHP MySQL extension.
3. Place the project in your server's document root.
4. Import the database schema.
5. Confirm the credentials in [`application/php/config/db_connection.php`](application/php/config/db_connection.php).
6. Load the app from `localhost`.

## Platform Notes

<details>
<summary><strong>macOS</strong></summary>

- XAMPP is the fastest path for local testing.
- If using Homebrew, install Apache, PHP, and MariaDB separately.
- Confirm the database service is running before opening the app.
</details>

<details>
<summary><strong>Windows</strong></summary>

- XAMPP or WAMP both work well.
- Place the project under `htdocs` or the equivalent web root.
- Run your terminal/editor with elevated permissions if file-permission issues come up.
</details>

<details>
<summary><strong>Linux</strong></summary>

- XAMPP, a manual LAMP stack, or Docker all work.
- If installing packages manually, make sure Apache has read access to the project files.
- Use the correct web root for your distribution.
</details>

## Configuration

The default database connection file is [`application/php/config/db_connection.php`](application/php/config/db_connection.php). It ships with local-only defaults (e.g. `root` with no password) — update these to match your environment. Since this file contains credentials, avoid committing real/non-default secrets to version control.

## Known Vulnerabilities (By Design)

This app intentionally includes the following weaknesses so they can be studied and exploited in a controlled lab, roughly mapped to the **OWASP Top 10**:

| Vulnerability | Where it shows up |
|---|---|
| **SQL Injection** | Database-backed endpoints using raw, unparameterized queries |
| **Cross-Site Scripting (XSS)** | Pages that insert server output into the DOM without escaping |
| **CSRF** | Forms and state-changing actions with no anti-CSRF tokens |
| **Plaintext password storage** | Registration/login flow does not hash passwords |
| **Broken access control** | Role-based pages/actions don't fully enforce authorization |
| **Weak/default credentials** | `db_connection.php` uses `root` for local setup |
| **Inconsistent output handling** | Some pages use safe DOM text handling; others don't — intentionally, to practice spotting the difference |

## Learning Goals

- Understand how common web vulnerabilities work in a realistic app context
- Practice safe testing and exploitation in a local environment
- Practice remediation: prepared statements, output encoding, CSRF tokens, password hashing (`password_hash`/`password_verify`), and proper role-based access control

## Suggested Workflow

1. Fork the project.
2. Run it locally on macOS, Windows, or Linux.
3. Explore the app's flows and identify the vulnerable ones.
4. Document what you find (write-ups make great portfolio material).
5. Patch the code in a separate branch to practice hardening.

## Forking This Project

Feel free to fork this and build on it for your own learning. If you do, please keep your fork private, or restrict use to an isolated lab environment, unless you've cleaned up the insecure parts first.

## Safe Usage Reminder

This project is for **offline learning and local testing only**. Do not deploy it on a public server or expose it to the internet.

## License

Personal and educational use only, unless a separate license is added later.