<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // allow all domains
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// URL of Core 3 API
$core3_api_url = "https://core3.slatefreight-ph.com/api/customers.php";

$method = $_SERVER['REQUEST_METHOD'];

if ($method === "GET") {
    // Forward GET request to Core 3
    $ch = curl_init($core3_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["error" => "cURL Error: " . curl_error($ch)]);
    } else {
        echo $response; // pass Core 3 JSON directly
    }
    curl_close($ch);
    exit;
}

if ($method === "POST") {
    // Forward POST request (if you want Core 1 to create shipments in Core 3)
    $data = file_get_contents("php://input");

    $ch = curl_init($core3_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Content-Length: " . strlen($data)
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo json_encode(["error" => "cURL Error: " . curl_error($ch)]);
    } else {
        echo $response;
    }
    curl_close($ch);
    exit;
}

http_response_code(405);
echo json_encode(["error" => "Unsupported request method"]);
exit;
