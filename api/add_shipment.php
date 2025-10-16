<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $po_id = $_POST['po_id'];
    $driver_name = $_POST['driver_name'];
    $vehicle_number = $_POST['vehicle_number'];
    $status = $_POST['status'];

    $sql = "INSERT INTO shipments (po_id, driver_name, vehicle_number, status)
          VALUES ('$po_id', '$driver_name', '$vehicle_number', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "Shipment booked successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
