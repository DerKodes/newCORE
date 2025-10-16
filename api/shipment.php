<?php
header("Content-Type: application/json");
include 'db.php';

$action = $_GET['action'] ?? 'list';

// ================== FETCH ALL SHIPMENTS ==================
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
  exit;
}

// ================== FETCH ARCHIVED SHIPMENTS ==================
elseif ($action === "archives") {
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
  exit;
}

// ================== ARCHIVE SHIPMENT ==================
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
  exit;
}

// ================== RESTORE SHIPMENT ==================
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
  exit;
}

// ================== DELETE SHIPMENT ==================
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
  exit;
}

// ================== UPDATE STATUS + AUTO TRACKING ==================
elseif ($action === 'updateStatus' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = $_POST['id'] ?? null;
  $status = $_POST['status'] ?? null;

  if (!$id || !$status) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
  }

  // Prevent setting In Transit if not consolidated
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

  // ✅ Update shipment status
  $stmt = $conn->prepare("UPDATE shipments SET status=? WHERE id=?");
  $stmt->bind_param("si", $status, $id);
  $success = $stmt->execute();
  $stmt->close();

  if ($success) {
    // ✅ Fetch shipment + PO details
    $info = $conn->query("
      SELECT s.id AS shipment_id, p.origin, p.destination
      FROM shipments s
      JOIN purchase_orders p ON s.po_id = p.id
      WHERE s.id = $id
      LIMIT 1
    ")->fetch_assoc();

    if ($info) {
      // Determine location
      $location = ($status === 'Ready')
        ? $info['origin']
        : (($status === 'Delivered') ? $info['destination'] : 'In Transit');

      // ✅ Insert tracking record (no duplicate same status)
      $tstmt = $conn->prepare("
        INSERT INTO shipment_tracking (shipment_id, location, status, updated_at)
        SELECT ?, ?, ?, NOW()
        WHERE NOT EXISTS (
          SELECT 1 FROM shipment_tracking WHERE shipment_id=? AND status=?
        )
      ");
      $tstmt->bind_param("issis", $info['shipment_id'], $location, $status, $info['shipment_id'], $status);
      $tstmt->execute();
      $tstmt->close();
    }

    echo json_encode(["success" => true, "message" => "Status updated and tracking synced"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to update"]);
  }
  exit;
}

// ================== CREATE SHIPMENT (auto Ready tracking) ==================
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
    $shipment_id = $stmt->insert_id;

    // ✅ Fetch PO info for tracking
    $info = $conn->query("SELECT origin, destination FROM purchase_orders WHERE id = $po_id")->fetch_assoc();
    $location = $info['origin'] ?? 'Unknown';

    // ✅ Insert initial tracking record
    $tstmt = $conn->prepare("
      INSERT INTO shipment_tracking (shipment_id, location, status, updated_at)
      VALUES (?, ?, ?, NOW())
    ");
    $tstmt->bind_param("iss", $shipment_id, $location, $status);
    $tstmt->execute();
    $tstmt->close();

    echo json_encode(["success" => true, "message" => "Shipment created and tracking initialized"]);
  } else {
    echo json_encode(["success" => false, "message" => "Failed to create shipment"]);
  }

  $stmt->close();
  exit;
}

// ================== SEARCH SHIPMENT BY PO NUMBER ==================
elseif ($action === 'search' && isset($_GET['po_number'])) {
  $po = $conn->real_escape_string($_GET['po_number']);

  $sql = "SELECT 
            s.id AS shipment_id,
            p.po_number,
            p.origin,
            p.destination,
            p.cargo_info,
            s.driver_name,
            s.vehicle_number,
            s.status AS shipment_status,
            CAST(s.consolidated AS UNSIGNED) AS consolidated,
            s.archived
          FROM shipments s
          JOIN purchase_orders p ON s.po_id = p.id
          WHERE p.po_number LIKE '%$po%'
          AND s.archived = 0";

  $res = $conn->query($sql);
  $shipments = [];

  while ($row = $res->fetch_assoc()) {
    $shipments[] = $row;
  }

  // If no shipments found → show TO BE TRACK
  if (empty($shipments)) {
    echo json_encode([
      "success" => true,
      "data" => [[
        "po_number" => $po,
        "shipment_status" => "TO BE TRACK",
        "origin" => null,
        "destination" => null,
        "driver_name" => null,
        "vehicle_number" => null
      ]]
    ]);
  } else {
    echo json_encode(["success" => true, "data" => $shipments]);
  }
  exit;
}

// ================== DEFAULT ==================
echo json_encode(["success" => false, "message" => "Invalid request"]);
$conn->close();
