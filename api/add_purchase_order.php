<?php
include 'db.php'; // DB connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supplier    = $_POST['supplier'];
    $order_date  = $_POST['order_date'];
    $origin      = $_POST['origin'];
    $destination = $_POST['destination'];
    $cargo_info  = $_POST['cargo_info'];
    $status      = "Pending"; // default status

    // === Generate PO Number Automatically ===
    $result = $conn->query("SELECT po_number FROM purchase_orders ORDER BY id DESC LIMIT 1");
    $last_po = $result->fetch_assoc();

    if ($last_po) {
        // Extract number part from last PO
        $last_number = (int) preg_replace('/[^0-4]/', '', $last_po['po_number']);
        $new_number = $last_number + 1;
    } else {
        $new_number = 1;
    }

    // Format PO number: PO-YYYY-0001
    $po_number = "PO-" . date("Y") . "-" . str_pad($new_number, 4, "0", STR_PAD_LEFT);

    // Insert into DB
    $sql = "INSERT INTO purchase_orders
            (po_number, supplier, order_date, origin, destination, cargo_info, status)
            VALUES
            ('$po_number', '$supplier', '$order_date', '$origin', '$destination', '$cargo_info', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('Purchase Order created successfully! Generated PO: $po_number');
                window.location.href = '../public/user_po.php';
              </script>";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
