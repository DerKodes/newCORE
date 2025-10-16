<?php
header("Content-Type: application/json");
include 'db.php';

// Utility: check if table exists
function tableExists($conn, $table) {
    $res = $conn->query("SHOW TABLES LIKE '$table'");
    return ($res && $res->num_rows > 0);
}

// Utility: check if column exists
function columnExists($conn, $table, $column) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return ($res && $res->num_rows > 0);
}

// Active Shipments (anything not delivered)
$shipments = $conn->query("SELECT COUNT(*) AS cnt FROM shipments WHERE status <> 'Delivered' OR status IS NULL");
$active_shipments = $shipments->fetch_assoc()['cnt'] ?? 0;

// Delivered Shipments
$delivered = $conn->query("SELECT COUNT(*) AS cnt FROM shipments WHERE status='Delivered'");
$delivered_shipments = $delivered->fetch_assoc()['cnt'] ?? 0;


// Open Consolidations
$open_consolidations = 0;
if (tableExists($conn, "consolidations")) {
    if (columnExists($conn, "consolidations", "status")) {
        $res = $conn->query("SELECT COUNT(*) AS cnt FROM consolidations WHERE status='Open'");
        $open_consolidations = $res->fetch_assoc()['cnt'] ?? 0;
    } else {
        $res = $conn->query("SELECT COUNT(*) AS cnt FROM consolidations");
        $open_consolidations = $res->fetch_assoc()['cnt'] ?? 0;
    }
}

// Total Consolidations
$total_consolidations = 0;
if (tableExists($conn, "consolidations")) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM consolidations");
    $total_consolidations = $res->fetch_assoc()['cnt'] ?? 0;
}

// Tracking Events in last 7 days
$tracking_events = 0;
if (tableExists($conn, "tracking_events") && columnExists($conn, "tracking_events", "event_time")) {
    $res = $conn->query("SELECT COUNT(*) AS cnt 
                         FROM tracking_events 
                         WHERE event_time >= NOW() - INTERVAL 7 DAY");
    $tracking_events = $res->fetch_assoc()['cnt'] ?? 0;
}

// Linked Purchase Orders
$linked_pos = 0;
if (tableExists($conn, "purchase_orders") && columnExists($conn, "purchase_orders", "status")) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM purchase_orders WHERE status='Linked'");
    $linked_pos = $res->fetch_assoc()['cnt'] ?? 0;
}

// Total Purchase Orders
$total_purchase_orders = 0;
if (tableExists($conn, "purchase_orders")) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM purchase_orders");
    $total_purchase_orders = $res->fetch_assoc()['cnt'] ?? 0;
}

// Master Bills of Lading
$master_bls = 0;
if (tableExists($conn, "master_bls")) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM master_bls");
    $master_bls = $res->fetch_assoc()['cnt'] ?? 0;
}

// House Bills of Lading
$house_bls = 0;
if (tableExists($conn, "house_bls")) {
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM house_bls");
    $house_bls = $res->fetch_assoc()['cnt'] ?? 0;
}

echo json_encode([
    "active_shipments" => $active_shipments,
    "delivered_shipments" => $delivered_shipments,
    "open_consolidations" => $open_consolidations,
    "total_consolidations" => $total_consolidations,
    "tracking_events" => $tracking_events,
    "linked_pos" => $linked_pos,
    "total_pos" => $total_purchase_orders,
    "master_bls" => $master_bls,
    "house_bls" => $house_bls
]);

$conn->close();
?>
