<?php
header("Content-Type: application/json");
include 'db.php';

$sql = "SELECT id, shipment_id, location, status, updated_at FROM shipment_tracking";
$result = $conn->query($sql);

$tracking = [];

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $tracking[] = $row;
  }
}

echo json_encode($tracking);

$conn->close();
