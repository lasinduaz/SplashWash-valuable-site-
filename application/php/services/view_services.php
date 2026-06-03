<?php
// /application/php/services/view_services.php

session_start();
require_once __DIR__ . '/../config/db_connection.php';

// FIX 1: Broken access control — previously any unauthenticated visitor could
//         view ALL service requests from ALL users. Now we require login and
//         filter by role: admins/technicians see all; customers see only their own.
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo "<div class='service'><p>Please log in to view services.</p></div>";
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? 'customer';

try {
    if ($role === 'admin' || $role === 'technician') {
        // Admins and technicians may see all requests
        $stmt = $pdo->query(
            "SELECT sr.*, u.username
               FROM service_requests sr
               LEFT JOIN users u ON sr.user_id = u.id
              ORDER BY sr.created_at DESC"
        );
    } else {
        // Customers see only their own requests
        $stmt = $pdo->prepare(
            "SELECT sr.*, u.username
               FROM service_requests sr
               LEFT JOIN users u ON sr.user_id = u.id
              WHERE sr.user_id = ?
              ORDER BY sr.created_at DESC"
        );
        $stmt->execute([$user_id]);
    }

    $rows = $stmt->fetchAll();

    if (empty($rows)) {
        echo "<div class='service'><p>No service requests found.</p></div>";
        exit;
    }

    foreach ($rows as $r) {
        // FIX 2: All output is now HTML-escaped to prevent stored XSS
        //         Original code echoed raw DB values directly into HTML
        $car_model   = htmlspecialchars($r['car_model'],           ENT_QUOTES, 'UTF-8');
        $username    = htmlspecialchars($r['username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($r['service_description'],  ENT_QUOTES, 'UTF-8');
        $status      = htmlspecialchars($r['status'],               ENT_QUOTES, 'UTF-8');

        echo "<div class='service'>";
        echo "<h4>{$car_model}</h4>";
        echo "<p>By: {$username} - {$description}</p>";
        echo "<p>Status: {$status}</p>";
        echo "</div>";
    }
} catch (\PDOException $e) {
    http_response_code(500);
    echo "<div class='service'><p>Error loading services.</p></div>";
}
