<?php
// api/hmb.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

// Core 2 endpoint
define('CORE2_URL', 'https://core2.slatefreight-ph.com/api/bl.php'); // <-- Update this to Core 2 actual URL

// Helper to return JSON
function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? 'list';

// ====================== LIST BLs ======================
if ($action === 'list') {
    $sql = "SELECT id, bl_number, type, shipment_id, shipper, consignee, created_at 
            FROM bills_of_lading 
            ORDER BY id DESC";
    $result = $conn->query($sql);
    $bls = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bls[] = [
                "id" => (int)$row["id"],
                "bl_number" => $row["bl_number"],
                "type" => $row["type"] ?? "HBL",
                "shipment_id" => $row["shipment_id"],
                "shipper" => $row["shipper"],
                "consignee" => $row["consignee"],
                "created_at" => $row["created_at"] ?? null,
            ];
        }
    }
    jsonResponse($bls);
}

// ====================== CREATE BL manually ======================
if ($action === 'create') {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        jsonResponse(["success" => false, "message" => "POST required"], 405);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        jsonResponse(["success" => false, "message" => "Invalid JSON"], 400);
    }

    $bl_number = $data["bl_number"] ?? "BL-" . time();

    $stmt = $conn->prepare("INSERT INTO bills_of_lading 
        (bl_number, type, shipment_id, shipper, consignee, origin, destination) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssissss",
        $bl_number,
        $data["type"] ?? "HBL",
        $data["shipment_id"],
        $data["shipper"],
        $data["consignee"],
        $data["origin"],
        $data["destination"]
    );

    if ($stmt->execute()) {
        $newBL = [
            "id" => $stmt->insert_id,
            "bl_number" => $bl_number,
            "type" => $data["type"] ?? "HBL",
            "shipment_id" => $data["shipment_id"],
            "shipper" => $data["shipper"],
            "consignee" => $data["consignee"],
            "origin" => $data["origin"],
            "destination" => $data["destination"],
        ];

        // Push to Core 2
        $ch = curl_init(CORE2_URL . '?action=receiveBL');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($newBL));
        $responseCore2 = curl_exec($ch);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $newBL["core2_push"] = "Failed: $curlErr";
        } else {
            $newBL["core2_push"] = json_decode($responseCore2, true);
        }

        jsonResponse([
            "success" => true,
            "message" => "BL created successfully and pushed to Core 2",
            "data" => $newBL
        ]);
    } else {
        jsonResponse(["success" => false, "message" => "DB error: " . $stmt->error], 500);
    }
}

// ====================== RECEIVE BL from Core 2 ======================
if ($action === 'receiveBL') {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        jsonResponse(["success" => false, "message" => "POST required"], 405);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        jsonResponse(["success" => false, "message" => "Invalid JSON"], 400);
    }

    if (!isset($data["bl_number"]) || !isset($data["shipment_id"])) {
        jsonResponse(["success" => false, "message" => "Missing required BL fields"], 422);
    }

    $stmt = $conn->prepare("INSERT INTO bills_of_lading 
        (bl_number, type, shipment_id, shipper, consignee, origin, destination, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssisssss",
        $data["bl_number"],
        $data["type"] ?? "HBL",
        $data["shipment_id"],
        $data["shipper"] ?? null,
        $data["consignee"] ?? null,
        $data["origin"] ?? null,
        $data["destination"] ?? null,
        $data["status"] ?? "RECEIVED"
    );

    if ($stmt->execute()) {
        jsonResponse([
            "success" => true,
            "message" => "BL received and stored in Core 1",
            "id" => $stmt->insert_id,
            "bl_number" => $data["bl_number"]
        ]);
    } else {
        jsonResponse(["success" => false, "message" => "DB error: " . $stmt->error], 500);
    }
}

jsonResponse(["success" => false, "message" => "Unknown action"], 400);

$conn->close();
