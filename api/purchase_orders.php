<?php
header("Content-Type: application/json");
include 'db.php'; // adjust if needed

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
  // Fetch only POs that don't yet have a shipment
  $sql = "
    SELECT po.*
    FROM purchase_orders po
    WHERE NOT EXISTS (
      SELECT 1 FROM shipments s WHERE s.po_id = po.id
    )
    ORDER BY po.id DESC
  ";

  $result = $conn->query($sql);
  $orders = [];
  while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
  }
  echo json_encode($orders);
  exit;
}


if ($method === "POST") {
  $data = json_decode(file_get_contents("php://input"), true);

  if (!$data) {
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
  }

  $supplier = $data['supplier'] ?? '';
  $order_date = $data['order_date'] ?? '';
  $status = $data['status'] ?? '';

  if ($supplier && $order_date && $status) {
    // Generate PO Number automatically
    // 1. Get the last inserted PO
    $result = $conn->query("SELECT po_number FROM purchase_orders ORDER BY id DESC LIMIT 1");
    $last_po = $result->fetch_assoc();

    if ($last_po) {
      // Extract number part
      $last_number = (int) preg_replace('/[^0-4]/', '', $last_po['po_number']);
      $new_number = $last_number + 1;
    } else {
      $new_number = 1;
    }

    // Format: PO-2025-0001
    $po_number = "PO-" . date("Y") . "-" . str_pad($new_number, 4, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO purchase_orders (po_number, supplier, order_date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $po_number, $supplier, $order_date, $status);

    if ($stmt->execute()) {
      echo json_encode([
        "message" => "PO created successfully",
        "po_number" => $po_number
      ]);
    } else {
      echo json_encode(["error" => "Database insert failed"]);
    }
  } else {
    echo json_encode(["error" => "Missing fields"]);
  }
  exit;
}
