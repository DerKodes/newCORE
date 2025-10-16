<?php
header("Content-Type: application/json");
include 'db.php';

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
  // Show suggested consolidations (only shipments not yet consolidated)
  $sql = "SELECT s.id, p.po_number, p.origin, p.destination, p.cargo_info AS cargo_details,
                   s.driver_name, s.vehicle_number, s.status, s.created_at
            FROM shipments s
            JOIN purchase_orders p ON s.po_id = p.id
            WHERE s.consolidated = 0
            ORDER BY p.destination, p.origin";
  $result = $conn->query($sql);

  $shipments = [];
  while ($row = $result->fetch_assoc()) {
    $shipments[] = $row;
  }

  echo json_encode($shipments);
}

// Create a consolidation
elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $shipment_ids = $_POST['shipment_ids'] ?? [];

  if (empty($shipment_ids)) {
    echo json_encode(["success" => false, "message" => "No shipments selected"]);
    exit;
  }

  // 1. Create consolidation record
  $conn->query("INSERT INTO consolidations (created_at) VALUES (NOW())");
  $consolidation_id = $conn->insert_id;

  // 2. Link shipments to consolidation
  $stmt = $conn->prepare("INSERT INTO consolidation_shipments (consolidation_id, shipment_id) VALUES (?, ?)");
  foreach ($shipment_ids as $sid) {
    $stmt->bind_param("ii", $consolidation_id, $sid);
    $stmt->execute();

    // Mark shipment as consolidated
    $update = $conn->prepare("UPDATE shipments SET consolidated = 1 WHERE id = ?");
    $update->bind_param("i", $sid);
    $update->execute();
    $update->close();
  }
  $stmt->close();

  echo json_encode(["success" => true, "message" => "Consolidation created successfully"]);
}

$conn->close();
