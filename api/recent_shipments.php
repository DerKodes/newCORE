<?php
include 'db.php';
header('Content-Type: application/json');

$data = [];

if (!$conn) {
  echo json_encode(["error" => "Database connection failed"]);
  exit;
}

$sql = "
  SELECT 
    s.id AS shipment_id,
    p.po_number,
    p.supplier,
    p.order_date,
    s.status,
    p.origin,
    p.destination,
    s.vehicle_number,
    s.created_at
  FROM shipments s
  JOIN purchase_orders p ON s.po_id = p.id
  ORDER BY s.created_at DESC
  LIMIT 5
";

$result = $conn->query($sql);

if (!$result) {
  echo json_encode(["error" => "Query failed: " . $conn->error]);
  exit;
}

while ($row = $result->fetch_assoc()) {
  // Generate Shipping Reference
  // Format: SH-YYYY-0001
  $ship_ref = "SH-" . date("Y", strtotime($row["created_at"])) . "-" . str_pad($row["shipment_id"], 4, "0", STR_PAD_LEFT);

  $data[] = [
    "shipping_ref"   => $ship_ref,
    "po_number"      => $row["po_number"],   // keep PO reference too if needed
    "supplier"       => $row["supplier"] ?: "N/A",
    "order_date"     => $row["order_date"] ?: "N/A",
    "status"         => $row["status"] ?: "N/A",
    "origin"         => $row["origin"] ?: "N/A",
    "destination"    => $row["destination"] ?: "N/A",
    "vehicle_number" => $row["vehicle_number"] ?: "N/A",
    "created_at"     => $row["created_at"] ?: "N/A"
  ];
}

echo json_encode($data);
