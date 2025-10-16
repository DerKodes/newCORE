<?php
header("Content-Type: application/json");
include 'db.php';

$shipment_id = $_GET['shipment_id'] ?? null;
$action = $_GET['action'] ?? null;

// ✅ Case 1: Fetch tracking logs or fallback shipment info
if ($shipment_id) {
  $shipment_id = intval($shipment_id);

  // Try to fetch tracking logs
  $sql = "SELECT id, shipment_id, location, status, updated_at 
          FROM shipment_tracking 
          WHERE shipment_id = $shipment_id
          ORDER BY updated_at DESC";

  $result = $conn->query($sql);
  $tracking = [];

  if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $tracking[] = $row;
    }
  } else {
    // Fallback: get shipment status if no tracking found
    $fallback = $conn->query("
      SELECT s.id AS shipment_id, p.origin, p.destination, s.status AS shipment_status
      FROM shipments s
      JOIN purchase_orders p ON s.po_id = p.id
      WHERE s.id = $shipment_id
      LIMIT 1
    ");

    if ($fallback && $fallback->num_rows > 0) {
      $info = $fallback->fetch_assoc();
      $tracking[] = [
        "shipment_id" => $info['shipment_id'],
        "location" => "{$info['origin']} → {$info['destination']}",
        "status" => $info['shipment_status'],
        "updated_at" => date("Y-m-d H:i:s")
      ];
    }
  }

  echo json_encode([
    "success" => true,
    "data" => $tracking
  ]);
  exit;
}

// ✅ Case 2: Fetch all tracking logs
// if ($action === 'list') {
//   $sql = "SELECT id, shipment_id, location, status, updated_at 
//           FROM shipment_tracking 
//           ORDER BY updated_at DESC";
//   $result = $conn->query($sql);
//   $tracking = [];
//   while ($row = $result->fetch_assoc()) {
//     $tracking[] = $row;
//   }
//   echo json_encode([
//     "success" => true,
//     "data" => $tracking
//   ]);
//   exit;
// }

// ✅ Default
echo json_encode(["success" => false, "message" => "Invalid request"]);
$conn->close();
