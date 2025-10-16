<?php
include 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$shipment_id = $_POST['shipment_id'] ?? null;
$type        = $_POST['type'] ?? 'HBL';

if (!$shipment_id) {
    echo json_encode(['error' => 'Shipment ID required']);
    exit;
}

// Fetch shipment info
$shipment = $conn->query("SELECT * FROM shipments WHERE id = $shipment_id")->fetch_assoc();
if (!$shipment) {
    echo json_encode(['error' => 'Shipment not found']);
    exit;
}

$shipper = $shipment['shipper'] ?? '';
$consignee = $shipment['consignee'] ?? '';
$cargo_info = $shipment['cargo_info'] ?? '';

// Generate BL number
if ($type === 'HBL') {
    $res = $conn->query("SELECT hbl_number FROM house_bills ORDER BY id DESC LIMIT 1");
    $last = $res->fetch_assoc();
    $num = $last ? (int) preg_replace('/[^0-9]/', '', $last['hbl_number']) : 0;
    $bl_number = "HBL-" . date("Y") . "-" . str_pad($num + 1, 4, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO house_bills (hbl_number, mbl_id, shipper, consignee, cargo_info, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issss", $shipment_id, $shipment_id, $shipper, $consignee, $cargo_info);
    $stmt->execute();
} else {
    $res = $conn->query("SELECT mbl_number FROM master_bills ORDER BY id DESC LIMIT 1");
    $last = $res->fetch_assoc();
    $num = $last ? (int) preg_replace('/[^0-9]/', '', $last['mbl_number']) : 0;
    $bl_number = "MBL-" . date("Y") . "-" . str_pad($num + 1, 4, "0", STR_PAD_LEFT);

    $stmt = $conn->prepare("INSERT INTO master_bills (mbl_number, shipment_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("si", $bl_number, $shipment_id);
    $stmt->execute();
}

echo json_encode(['success' => true, 'bl_number' => $bl_number]);
