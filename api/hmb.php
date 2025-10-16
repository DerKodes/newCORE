<?php
// api/hmb.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

include 'db.php';

// Core 2 endpoint
define('CORE2_URL', 'https://core2.slatefreight-ph.com/api/bl.php');

// JSON Response Helper
function jsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$action = $_GET['action'] ?? 'list';

// ====================== LIST BLs ======================
if ($action === 'list') {
    $sql = "SELECT id, bl_number, type, shipment_id, shipper, consignee, origin, destination, created_at 
            FROM bills_of_lading 
            ORDER BY id DESC";
    $result = $conn->query($sql);
    $bls = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bls[] = [
                "id" => (int)$row["id"],
                "bl_number" => $row["bl_number"],
                "type" => $row["type"],
                "shipment_id" => (int)$row["shipment_id"],
                "shipper" => $row["shipper"],
                "consignee" => $row["consignee"],
                "origin" => $row["origin"],
                "destination" => $row["destination"],
                "created_at" => $row["created_at"],
            ];
        }
    }

    jsonResponse(["success" => true, "data" => $bls]);
}

// ====================== CREATE BL ======================
if ($action === 'create') {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        jsonResponse(["success" => false, "message" => "POST required"], 405);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        jsonResponse(["success" => false, "message" => "Invalid JSON"], 400);
    }

    // Prepare data
    $bl_number = $data["bl_number"] ?? "BL-" . time();
    $type = $data["type"] ?? "HBL";
    $shipment_id = $data["shipment_id"] ?? null;
    $shipper = $data["shipper"] ?? null;
    $consignee = $data["consignee"] ?? null;
    $origin = $data["origin"] ?? null;
    $destination = $data["destination"] ?? null;

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO bills_of_lading 
        (bl_number, type, shipment_id, shipper, consignee, origin, destination)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissss", $bl_number, $type, $shipment_id, $shipper, $consignee, $origin, $destination);

    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;

        // Prepare data for Core 2 push
        $newBL = [
            "id" => $new_id,
            "bl_number" => $bl_number,
            "type" => $type,
            "shipment_id" => $shipment_id,
            "shipper" => $shipper,
            "consignee" => $consignee,
            "origin" => $origin,
            "destination" => $destination,
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
            $core2_result = ["success" => false, "error" => $curlErr];
        } else {
            $core2_result = json_decode($responseCore2, true) ?? ["response" => $responseCore2];
        }

        jsonResponse([
            "success" => true,
            "message" => "BL created successfully",
            "data" => $newBL,
            "core2_push" => $core2_result
        ]);
    } else {
        jsonResponse(["success" => false, "message" => "DB error: " . $stmt->error], 500);
    }
}

// ====================== RECEIVE BL from CORE 2 ======================
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
        (bl_number, type, shipment_id, shipper, consignee, origin, destination)
        VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssissss",
        $data["bl_number"],
        $data["type"] ?? "HBL",
        $data["shipment_id"],
        $data["shipper"] ?? null,
        $data["consignee"] ?? null,
        $data["origin"] ?? null,
        $data["destination"] ?? null
    );

    if ($stmt->execute()) {
        jsonResponse([
            "success" => true,
            "message" => "BL received and stored in Core 1",
            "id" => $stmt->insert_id
        ]);
    } else {
        jsonResponse(["success" => false, "message" => "DB error: " . $stmt->error], 500);
    }
}

// ====================== Unknown Action ======================
jsonResponse(["success" => false, "message" => "Unknown action"], 400);

$conn->close();
?>
