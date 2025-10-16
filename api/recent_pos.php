<?php
include 'db.php';
header('Content-Type: application/json');

$sql = "SELECT id, po_number, supplier, status, order_date
        FROM purchase_orders
        ORDER BY order_date DESC
        LIMIT 5";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
