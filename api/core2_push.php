<?php
header("Content-Type: application/json");

$core2_api_url = "https://core2.slatefreight-ph.com/api/shipments.php";
$method = $_SERVER['REQUEST_METHOD'];

// Forward GET requests from Core1 → Core2
if ($method === "GET") {
    $ch = curl_init($core2_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["error" => curl_error($ch)]);
        exit;
    }
    curl_close($ch);
    echo $response;
    exit;
}

// Forward POST requests (pushing shipments from Core1 to Core2)
if ($method === "POST") {
    $data = file_get_contents("php://input");

    $ch = curl_init($core2_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($data)
    ]);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["error" => curl_error($ch)]);
        exit;
    }
    curl_close($ch);
    echo $response;
    exit;
}

echo json_encode(["error" => "Unsupported request method"]);
