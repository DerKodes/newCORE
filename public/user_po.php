<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Purchase Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5">
        <div class="card p-4 shadow-sm">
            <h3>Create Purchase Order</h3>
            <form action="../api/add_purchase_order.php" method="POST">
                <div class="mb-3">
                    <label for="supplier" class="form-label">Supplier Name</label>
                    <input type="text" class="form-control" id="supplier" name="supplier" required>
                </div>
                <div class="mb-3">
                    <label for="order_date" class="form-label">Order Date</label>
                    <input type="date" class="form-control" id="order_date" name="order_date" required>
                </div>
                <div class="mb-3">
                    <label for="origin" class="form-label">Origin</label>
                    <input type="text" class="form-control" id="origin" name="origin" required>
                </div>
                <div class="mb-3">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" class="form-control" id="destination" name="destination" required>
                </div>
                <div class="mb-3">
                    <label for="cargo_info" class="form-label">Cargo Info</label>
                    <textarea class="form-control" id="cargo_info" name="cargo_info" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit PO</button>
            </form>
        </div>
    </div>

</body>

</html>
