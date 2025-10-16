<?php
header("Content-Type: application/json");
include 'db.php';

$action = $_GET['action'] ?? 'list';

// Fetch shipments (all or only unconsolidated)
if ($action === 'list') {
  $sql = "SELECT s.id, p.po_number, p.origin, p.destination, p.cargo_info,
               s.driver_name, s.vehicle_number, s.status,
               CAST(s.consolidated AS UNSIGNED) AS consolidated,
               s.archived
        FROM shipments s
        JOIN purchase_orders p ON s.po_id = p.id
        WHERE s.archived = 0
        ORDER BY s.created_at DESC";

  $result = $conn->query($sql);

  $shipments = [];
  while ($row = $result->fetch_assoc()) {
    $shipments[] = $row;
  }

  echo json_encode($shipments);
}

if ($action === "archives") {
  $sql = "SELECT s.id, p.po_number, p.origin, p.destination, p.cargo_info,
                   s.driver_name, s.vehicle_number, s.status, s.archived, s.created_at
            FROM shipments s
            JOIN purchase_orders p ON s.po_id = p.id
            WHERE s.archived = 1
            ORDER BY s.created_at DESC";

  $result = $conn->query($sql);

  $shipments = [];
  while ($row = $result->fetch_assoc()) {
    $shipments[] = $row;
  }

  echo json_encode($shipments);
}

// Archive shipment
elseif ($action === 'archive' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;

  if (!$id) {
    echo json_encode(["success" => false, "message" => "Invalid shipment ID"]);
    exit;
  }

  $stmt = $conn->prepare("UPDATE shipments SET archived = 1 WHERE id=?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Shipment archived"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to archive"]);
  }
  $stmt->close();
}

// Restore shipment
elseif ($action === 'restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? $_GET['id'] ?? null;

  if (!$id) {
    echo json_encode(["success" => false, "message" => "Invalid shipment ID"]);
    exit;
  }

  $stmt = $conn->prepare("UPDATE shipments SET archived = 0, status = 'Delivered' WHERE id=?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Shipment restored"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to restore"]);
  }
  $stmt->close();
}

// Permanently delete shipment
elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? $_GET['id'] ?? null;

  if (!$id) {
    echo json_encode(["success" => false, "message" => "Invalid shipment ID"]);
    exit;
  }

  $stmt = $conn->prepare("DELETE FROM shipments WHERE id=?");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Shipment permanently deleted"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to delete"]);
  }
  $stmt->close();
}

// Update shipment status
elseif ($action === 'updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  $status = $_POST['status'] ?? null;

  if (!$id || !$status) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
  }

  // Block setting In Transit if not consolidated
  if ($status === "In Transit") {
    $check = $conn->prepare("SELECT consolidated FROM shipments WHERE id=?");
    $check->bind_param("i", $id);
    $check->execute();
    $check->bind_result($consolidated);
    $check->fetch();
    $check->close();

    if ($consolidated != 1) {
      echo json_encode([
        "success" => false,
        "message" => "Shipment must be consolidated before setting In Transit"
      ]);
      exit;
    }
  }

  $stmt = $conn->prepare("UPDATE shipments SET status=? WHERE id=?");
  $stmt->bind_param("si", $status, $id);

  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Status updated"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to update"]);
  }
  $stmt->close();
}

// Create shipment
elseif ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $po_id = $_POST['po_id'] ?? null;
  $driver = $_POST['driver_name'] ?? '';
  $vehicle = $_POST['vehicle_number'] ?? '';
  $status = $_POST['status'] ?? 'Ready';

  if (!$po_id) {
    echo json_encode(["success" => false, "message" => "PO is required"]);
    exit;
  }

  $stmt = $conn->prepare("INSERT INTO shipments (po_id, driver_name, vehicle_number, status, consolidated) VALUES (?, ?, ?, ?, 0)");
  $stmt->bind_param("isss", $po_id, $driver, $vehicle, $status);

  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Shipment created successfully"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to create shipment"]);
  }

  $stmt->close();
}

$conn->close();
