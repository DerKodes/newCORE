<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CORE 1 Dashboard</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo"><img src="../assets/slate.png" alt="Logo"></div>
    <div class="system-name">CORE TRANSACTION 1</div>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="purchase_orders.php">Purchase Orders</a>
    <a href="shipment.php">Shipment Booking</a>
    <a href="hmb.php">BL Generator</a>
    <a href="consolidation.php">Consolidation</a>
    <a href="ship_tracking.php">Tracking</a>
    <a href="archives.php">Archives</a>
  </div>

  <!-- Main Content -->
  <div class="content" id="content">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <h1 id="pageTitle">Dashboard <span class="system-title">| CORE 1</span></h1>
      <div class="theme-toggle-container">
        <div class="dropdown">
          <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown">
            <i class="bi bi-person"></i>
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#">Profile</a></li>
            <li><a class="dropdown-item" onclick="confirmLogout()">Logout</a></li>
          </ul>
        </div>
        <span class="theme-label">Dark Mode</span>
        <label class="theme-switch">
          <input type="checkbox" id="themeToggle">
          <span class="slider"></span>
        </label>
      </div>
    </div>



    <!-- Page Content -->

    <div id="page-hmb">

      <!-- BL Generator Card -->
      <div class="card mb-4">
        <div class="card-header">
          <strong>Generate New BL</strong>
        </div>
        <div class="card-body">
          <form id="createForm" class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Shipment</label>
              <select id="shipmentSelect" name="shipment_id" class="form-select" required>
                <option value="">Select a shipment...</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Type</label>
              <select id="blType" name="type" class="form-select" required>
                <option value="">Choose...</option>
                <option value="HBL">HBL</option>
                <option value="MBL">MBL</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">BL Number</label>
              <input type="text" id="blNumber" name="bl_number" class="form-control" placeholder="Auto-generated" readonly>
            </div>

            <div class="col-md-6">
              <label class="form-label">Consignee</label>
              <input type="text" id="blConsignee" name="consignee" class="form-control" placeholder="Auto-filled from shipment">
            </div>

            <div class="col-md-6">
              <label class="form-label">Shipper</label>
              <input type="text" id="blShipper" name="shipper" class="form-control" placeholder="Auto-filled from shipment">
            </div>

            <div class="col-md-6">
              <label class="form-label">Weight</label>
              <input type="text" id="blShipper" name="shipper" class="form-control" placeholder="Auto-filled from shipment" readonly>
            </div>

            <div class="col-12 text-end">
              <button type="submit" class="btn btn-primary">Generate BL</button>
            </div>
          </form>
        </div>
      </div>

      <!-- BL List Table -->
      <div class="card">
        <div class="card-header">
          <strong>Generated BLs</strong>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="blTable" class="table table-striped table-hover">
              <thead>
                <tr>
                  <th>BL Number</th>
                  <th>Type</th>
                  <th>Shipment</th>
                  <th>Consignee</th>
                  <th>Shipper</th>
                  <th>Issue Date</th>
                  <th>Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Hidden BL Sticker Template -->
      <div id="blSticker" style="display:none; width:400px; border:2px solid black; font-family:Arial, sans-serif;">
        <h4 style="text-align:center; font-weight:bold;">BILL OF LADING</h4>
        <div id="qrCode" style="text-align:center; margin:10px 0;"></div>
        <hr>
        <p><strong>BL Number:</strong> <span id="stickerBlNo"></span></p>
        <p><strong>Type:</strong> <span id="stickerType"></span></p>
        <p><strong>Shipment:</strong> <span id="stickerShipment"></span></p>
        <p><strong>Consignee:</strong> <span id="stickerConsignee"></span></p>
        <p><strong>Shipper:</strong> <span id="stickerShipper"></span></p>
        <p><strong>Origin → Destination:</strong> <span id="stickerRoute"></span></p>
        <p><strong>Issue Date:</strong> <span id="stickerDate"></span></p>
        <hr>
        <p class="footer">📦 CORE 1 Freight Management</p>
      </div>
    </div>




  </div>


  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="../scripts/hmb.js"></script>
  <script src="../scripts/app.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
  <script>
    function confirmLogout() {
      swal({
        title: "Are you sure?",
        text: "Do you want to logout?",
        icon: "warning",
        buttons: ["No, stay", "Yes, logout"],
        dangerMode: true,
      }).then((willLogout) => {
        if (willLogout) {
          window.location.href = "../login/logout.php";
        }
      });
    }

    <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Login successful 🎉',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
      });
      <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>
  </script>
</body>

</html>