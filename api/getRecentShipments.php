<?php
require_once "db.php"; // adjust path to your DB connection

header('Content-Type: application/json');

$sql = "SELECT ref, type, mode, status, origin, destination, eta 
        FROM shipments 
        ORDER BY updated_at DESC 
        LIMIT 10";  // adjust table/columns as per your schema
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
$conn->close();
