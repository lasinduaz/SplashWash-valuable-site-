<?php
// /application/php/services/create_service.php

session_start();
require_once __DIR__ . '/../config/db_connection.php';

// FIX 1: Return proper HTTP status on auth failure instead of just echoing
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "Not authenticated.";
    exit;
}

$user_id    = (int) $_SESSION['user_id']; // FIX 2: Cast to int — never trust session data untyped
$car_model  = trim($_POST['car_model'] ?? '');
$description = trim($_POST['service_description'] ?? '');

// FIX 3: Server-side input validation (was completely absent)
if ($car_model === '' || $description === '') {
    http_response_code(400);
    echo "Car model and description are required.";
    exit;
}

if (strlen($car_model) > 255) {
    http_response_code(400);
    echo "Car model name is too long (max 255 chars).";
    exit;
}

// FIX 4: Use a prepared statement to prevent SQL Injection
//         Original: "INSERT INTO service_requests (user_id, car_model, service_description) VALUES ($user_id, '$car_model', '$description')"
try {
    $stmt = $pdo->prepare(
        "INSERT INTO service_requests (user_id, car_model, service_description) VALUES (?, ?, ?)"
    );
    $stmt->execute([$user_id, $car_model, $description]);
    echo "Service request created.";
} catch (\PDOException $e) {
    http_response_code(500);
    echo "Error creating service request. Please try again.";
}
