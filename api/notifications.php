<?php
include 'db.php';
header('Content-Type: application/json');

$sql = "
  (SELECT 
      'Purchase Order' AS title,
      CONCAT('New Purchase Order #', p.po_number, ' from ', p.supplier) AS message,
      p.order_date AS time
   FROM purchase_orders p
   ORDER BY p.order_date DESC
   LIMIT 5)
UNION
  (SELECT 
      'Shipment Update' AS title,
      CONCAT('Shipment #', s.id, ' (PO ', p.po_number, ', ', p.supplier, ') is now ', s.status) AS message,
      s.created_at AS time
   FROM shipments s
   JOIN purchase_orders p ON s.po_id = p.id
   ORDER BY s.created_at DESC
   LIMIT 5)
ORDER BY time DESC
LIMIT 5
";

$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
  $data[] = [
    "title" => $row['title'],
    "message" => $row['message'],
    "time" => $row['time']
  ];
}
echo json_encode($data);
