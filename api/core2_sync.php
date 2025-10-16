<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // allow Core 2 to fetch

include 'db.php'; // Core 1 DB connection

// Fetch shipments (only active, not archived)
$sql = "SELECT s.id, p.po_number, p.origin, p.destination, p.cargo_info,
               s.driver_name, s.vehicle_number, s.status,
               CAST(s.consolidated AS UNSIGNED) AS consolidated,
               s.archived, s.created_at
        FROM shipments s
        JOIN purchase_orders p ON s.po_id = p.id
        WHERE s.archived = 0
        ORDER BY s.created_at DESC";

$result = $conn->query($sql);

$shipments = [];
while ($row = $result->fetch_assoc()) {
    // Map Core 1 fields → Core 2 friendly format
    $shipments[] = [
        "route_id"      => $row["id"],              // Core 2 expects routes
        "po_number"     => $row["po_number"],
        "origin"        => $row["origin"],
        "destination"   => $row["destination"],
        "cargo_info"    => $row["cargo_info"],
        "driver_name"   => $row["driver_name"],
        "vehicle_number"=> $row["vehicle_number"],
        "status"        => $row["status"],
        "consolidated"  => $row["consolidated"],
        "archived"      => $row["archived"],
        "created_at"    => $row["created_at"]
    ];
}

echo json_encode($shipments);
$conn->close();
