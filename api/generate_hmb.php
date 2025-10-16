<?php
include 'db.php';
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shipment_id = $_POST['shipment_id'] ?? null;
    $shipper     = $_POST['shipper'] ?? '';
    $consignee   = $_POST['consignee'] ?? '';
    $cargo_info  = $_POST['cargo_info'] ?? '';

    if (!$shipment_id) {
        echo json_encode(["error" => "Shipment ID is required"]);
        exit;
    }

    // === Generate Master Bill Number ===
    $res = $conn->query("SELECT mbl_number FROM master_bills ORDER BY id DESC LIMIT 1");
    $last_mbl = $res->fetch_assoc();
    $last_num = $last_mbl ? (int) preg_replace('/[^0-9]/', '', $last_mbl['mbl_number']) : 0;
    $mbl_number = "MBL-" . date("Y") . "-" . str_pad($last_num + 1, 4, "0", STR_PAD_LEFT);

    // Insert Master Bill
    $conn->query("INSERT INTO master_bills (mbl_number, shipment_id) VALUES ('$mbl_number', '$shipment_id')");
    $mbl_id = $conn->insert_id;

    // === Generate House Bill Number ===
    $res2 = $conn->query("SELECT hbl_number FROM house_bills ORDER BY id DESC LIMIT 1");
    $last_hbl = $res2->fetch_assoc();
    $last_num_hbl = $last_hbl ? (int) preg_replace('/[^0-9]/', '', $last_hbl['hbl_number']) : 0;
    $hbl_number = "HBL-" . date("Y") . "-" . str_pad($last_num_hbl + 1, 4, "0", STR_PAD_LEFT);

    // Insert House Bill
    $conn->query("INSERT INTO house_bills (hbl_number, mbl_id, shipper, consignee, cargo_info) 
                  VALUES ('$hbl_number', '$mbl_id', '$shipper', '$consignee', '$cargo_info')");

    echo json_encode([
        "message" => "HMB generated successfully",
        "master_bill" => $mbl_number,
        "house_bill" => $hbl_number
    ]);
}
