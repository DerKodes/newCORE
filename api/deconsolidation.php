<?php
header("Content-Type: application/json");
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    $sql = "SELECT * FROM deconsolidations ORDER BY id DESC";
    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

if ($method === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    $consolidation_id = $data['consolidation_id'] ?? '';
    $shipment_id = $data['shipment_id'] ?? '';
    $status = $data['status'] ?? 'Released';

    $stmt = $conn->prepare("INSERT INTO deconsolidations (consolidation_id, shipment_id, status, deconsolidated_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $consolidation_id, $shipment_id, $status);

    if ($stmt->execute()) {
        echo json_encode(["message" => "Deconsolidation recorded"]);
    } else {
        echo json_encode(["error" => "Insert failed"]);
    }
    exit;
}
