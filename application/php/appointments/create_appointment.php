<?php
// /application/php/appointments/create_appointment.php

session_start();
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../config/input_security.php';

// FIX 1 Proper HTTP status on auth failure
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo "Not authorized.";
    exit;
}

// FIX 2 Type-safe extraction and validation (was raw $_POST with no checks)
$request_id    = filter_input(INPUT_POST, 'request_id',    FILTER_VALIDATE_INT);
$assigned_to   = filter_input(INPUT_POST, 'assigned_to',   FILTER_VALIDATE_INT);
$scheduled_for = trim($_POST['scheduled_for'] ?? '');
$notes         = trim($_POST['notes'] ?? '');

block_sql_injection([$scheduled_for, $notes], 'create_appointment');

// FIX 3 Server-side validation — all required fields must be present and valid
if (!$request_id || !$scheduled_for) {
    http_response_code(400);
    echo "Request ID and scheduled date/time are required.";
    exit;
}

// FIX 4 Validate datetime format to prevent garbage data
$dt = \DateTime::createFromFormat('Y-m-d H:i:s', $scheduled_for)
    ?: \DateTime::createFromFormat('Y-m-d\TH:i', $scheduled_for);
if (!$dt) {
    http_response_code(400);
    echo "Invalid date/time format. Use YYYY-MM-DD HH:MM:SS.";
    exit;
}
$scheduled_for = $dt->format('Y-m-d H:i:s'); // normalize

// FIX 5 Use prepared statement to prevent SQL Injection
//         Original: "INSERT INTO appointments ...
//  VALUES ($request_id, '$scheduled_for', $assigned_to, '$notes')"
//         — unquoted integers allowed direct injection
try {
    $stmt = $pdo->prepare(
        "INSERT INTO appointments (request_id, scheduled_for, assigned_to, notes)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->execute([
        $request_id,
        $scheduled_for,
        $assigned_to ?: null, // NULL when unassigned is valid per schema
        $notes
    ]);
    echo "Appointment created.";
} catch (\PDOException $e) {
    http_response_code(500);
    echo "Error creating appointment. Please try again.";
}
