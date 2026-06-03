<?php
// /application/php/auth/register.php

session_start();
require_once __DIR__ . '/../config/db_connection.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$role     = $_POST['role'] ?? 'customer';

// FIX 1: Input validation - username and password must not be empty
if ($username === '' || $password === '') {
    http_response_code(400);
    echo "Username and password are required.";
    exit;
}

// FIX 2: Username length and character whitelist
if (strlen($username) < 3 || strlen($username) > 100) {
    http_response_code(400);
    echo "Username must be 3–100 characters.";
    exit;
}

// FIX 3: Minimum password length
if (strlen($password) < 6) {
    http_response_code(400);
    echo "Password must be at least 6 characters.";
    exit;
}

// FIX 4: Role whitelist — prevent escalating to 'admin' via POST manipulation
$allowed_roles = ['customer', 'technician'];
if (!in_array($role, $allowed_roles, true)) {
    $role = 'customer';
}

// FIX 5: Hash the password before storing (was stored as plaintext)
$hashed = password_hash($password, PASSWORD_BCRYPT);

// FIX 6: Use a prepared statement to prevent SQL Injection
//         Original: "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')"
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashed, $role]);
    echo "Registered user: " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
} catch (\PDOException $e) {
    // FIX 7: Don't expose raw DB errors (e.g., duplicate key) to the client
    if ($e->getCode() === '23000') {
        http_response_code(409);
        echo "Username already taken.";
    } else {
        http_response_code(500);
        echo "Registration failed. Please try again.";
    }
}
