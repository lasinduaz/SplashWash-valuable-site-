<?php
// /application/php/auth/login.php

// FIX 1: session_start() must come BEFORE any logic or output
session_start();

// FIX 2: Removed the dead unreachable block that referenced $login_successful
//         (it was above require_once and never set, causing a logical dead-code error)

require_once __DIR__ . '/../config/db_connection.php';

// FIX 3: Use null-coalescing operator (cleaner, PHP 7+)
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// FIX 4: Basic input presence check
if ($username === '' || $password === '') {
    http_response_code(400);
    echo "Please provide both username and password.";
    exit;
}

// FIX 5: Use a prepared statement to prevent SQL Injection
//         Original: "SELECT * FROM users WHERE username = '$username' AND password = '$password'"
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// FIX 6: Verify password with password_verify() against a hash.
//         For the demo seed data (plaintext), we fall back to direct comparison
//         but the architecture is now correct for hashed passwords.
//         In production: only password_verify() should be used.
if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
    // FIX 7: Regenerate session ID on login to prevent session fixation attacks
    session_regenerate_id(true);

    $_SESSION['user_id']  = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role']     = $user['role'];

    echo "Login successful. Hello, " . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8');
} else {
    http_response_code(401);
    echo "Invalid credentials.";
}
