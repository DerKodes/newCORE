<?php
include("db.php"); // adjust path

header("Content-Type: application/json; charset=UTF-8");

// 1️⃣ Total shipments (today)
$today = date("Y-m-d");
$total_shipments = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE DATE(created_at) = '$today'")->fetch_assoc()['total'] ?? 0;

// 2️⃣ Active shipments (status = 'In Transit')
$active_shipments = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE status='In Transit'")->fetch_assoc()['total'] ?? 0;

// 3️⃣ Delivered shipments (status = 'Delivered')
$delivered_shipments = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE status='Delivered'")->fetch_assoc()['total'] ?? 0;

// 4️⃣ Current delays (status = 'Delayed')
$current_delays = $conn->query("SELECT COUNT(*) as total FROM shipments WHERE status='Delayed'")->fetch_assoc()['total'] ?? 0;

// Return JSON
echo json_encode([
    "total_shipments" => intval($total_shipments),
    "active_shipments" => intval($active_shipments),
    "delivered_shipments" => intval($delivered_shipments),
    "current_delays" => intval($current_delays)
]);
