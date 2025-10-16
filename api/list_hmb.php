<?php
include 'db.php';
header('Content-Type: application/json');

$bls = [];

// HBLs
$hblQuery = "
    SELECT 
        hb.id,
        hb.hbl_number AS bl_number,
        'HBL' AS type,
        hb.shipper,
        hb.consignee,
        hb.cargo_info,
        hb.mbl_id AS shipment_id,
        hb.created_at,
        'ISSUED' AS status
    FROM house_bills hb
    LEFT JOIN shipments s ON hb.mbl_id = s.id
";

// MBLs
$mblQuery = "
    SELECT
        mb.id,
        mb.mbl_number AS bl_number,
        'MBL' AS type,
        s.shipper,
        s.consignee,
        s.cargo_info,
        mb.shipment_id,
        mb.created_at,
        'ISSUED' AS status
    FROM master_bills mb
    LEFT JOIN shipments s ON mb.shipment_id = s.id
";

// Combine & order
$query = "($hblQuery) UNION ($mblQuery) ORDER BY created_at DESC";
$res = $conn->query($query);

while ($row = $res->fetch_assoc()) {
    $bls[] = $row;
}

echo json_encode($bls);
