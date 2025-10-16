<?php
// Force secure cookie settings
ini_set('session.cookie_httponly', 1); // Prevent JavaScript access to session
ini_set('session.use_only_cookies', 1); // Disallow session ID in URL
ini_set('session.use_strict_mode', 1);  // Reject uninitialized session IDs

// If your site runs on HTTPS, enforce secure cookies
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
  ini_set('session.cookie_secure', 1);
}

// Start session
session_start();

// OPTIONAL: regenerate ID on each request after login
if (!isset($_SESSION["initiated"])) {
  session_regenerate_id(true);
  $_SESSION["initiated"] = true;
}

// Require login
if (!isset($_SESSION["Email"])) {
  header("Location: login/login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CORE 1 Dashboard</title>
  <link rel="stylesheet" href="assets/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo"><img src="assets/slate.png" alt="Logo"></div>
    <div class="system-name">CORE 1</div>
    <a href="#page-dashboard" class="active">Dashboard</a>
    <a href="#page-purchase-orders">Purchase Orders</a>
    <a href="#page-shipment">Shipment Booking</a>
    <a href="#page-hmb">BL Generator</a>
    <a href="#page-consolidation">Consolidation</a>
    <a href="#page-ship-tracking">Tracking</a>
    <a href="#page-archives">Archives</a>
  </div>

  <!-- Main Content -->
  <div class="content" id="content">
    <!-- Header -->
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

    <!-- Module Navigation
    <nav class="module-nav">
      <a href="#page-dashboard" class="active">Dashboard</a>
      <a href="#page-purchase-orders">Purchase Orders</a>
      <a href="#page-shipment">Shipment Booking</a>
      <a href="#page-consolidation">Consolidation</a>
      <a href="#page-hmb">BL Generator</a>
      <a href="#page-ship-tracking">Tracking</a>
      <a href="#page-archives">Archives</a>
    </nav> -->

    <div id="page-dashboard" class="page active">
      <div class="dashboard-cards">
        <div class="card">
          <div class="stat-value" id="kpiShipments">0</div>
          <div>Active Shipments</div>
        </div>
        <div class="card">
          <div class="stat-value" id="kpiConsol">0</div>
          <div>Open Consolidations</div>
        </div>
        <div class="card">
          <div class="stat-value" id="kpiEvents">0</div>
          <div>Tracking Events</div>
        </div>
        <div class="card">
          <div class="stat-value" id="kpiPOs">0</div>
          <div>Linked POs</div>
        </div>
      </div>

      <div class="row g-3 mt-2">
        <!-- Recently Updated Shipments -->
        <div class="col-lg-8">
          <div class="card h-100">
            <div class="card-header">
              <strong>Recently Updated Shipments</strong>
            </div>
            <div class="table-responsive">
              <table class="table table-hover table-striped align-middle mb-0" id="tblRecent">
                <thead class="table-header">
                  <tr>
                    <th>Shipment Ref</th>
                    <th>Supplier</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>Vehicle Number</th>
                    <th>Created At</th>
                  </tr>
                </thead>
                <tbody id="recentShipments">
                  <!-- Filled dynamically by app.js -->
                </tbody>
              </table>
            </div>
          </div>
        </div>


        <!-- Notifications -->
        <div class="col-lg-4">
          <div class="card h-100">
            <div class="card-header">
              <strong>History</strong>
            </div>
            <div class="list-group list-group-flush" id="listNotifs">
              <!-- Filled dynamically by app.js -->
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- GRAPH SECTION -->


    <div id="page-purchase-orders" class="page">
      <div class="table-section">
        <table id="poTable" class="table table-striped">
          <thead>
            <tr>
              <th>PO #</th>
              <th>Supplier</th>
              <th>Date</th>
              <th>Origin</th>
              <th>Destination</th>
              <th>Cargo</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <!-- Create Shipment Modal -->
    <div class="modal fade" id="shipmentModal" tabindex="-1" aria-labelledby="shipmentModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="shipmentModalLabel">Create Shipment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="shipmentForm">
              <div class="mb-3">
                <label class="form-label">Select PO</label>
                <select class="form-select" name="po_id" id="poSelect" required></select>
              </div>
              <div class="mb-3">
                <input type="text" class="form-control" name="driver_name" placeholder="Driver Name">
              </div>
              <div class="mb-3">
                <input type="text" class="form-control" name="vehicle_number" placeholder="Vehicle Number">
              </div>
              <input type="hidden" name="status" value="Ready">
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="shipmentForm" class="btn btn-primary">Create Shipment</button>
          </div>
        </div>
      </div>
    </div>


    <!-- Shipment Booking Section -->
    <div id="page-shipment" class="page">
      <div class="table-section">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h3>All Shipments</h3>
          <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#shipmentModal">
            New Shipment
          </button>
        </div>
        <table id="shipmentTable" class="table table-striped">
          <thead>
            <tr>
              <th>PO</th>
              <th>Origin</th>
              <th>Destination</th>
              <th>Cargo</th>
              <th>Driver</th>
              <th>Vehicle</th>
              <th>Status</th>
              <th class="text-center align-middle">Consolidated</th>
              <th class="text-center align-middle">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <div id="page-hmb" class="page">

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
                <!-- Populated dynamically by app.js -->
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




    <div id="page-consolidation" class="page">
      <form id="consolidationForm" class="form mb-3">
        <div class="table-section">
          <button type="submit" class="btn btn-primary mt-2">Create Consolidation</button>
          <table id="suggestedTable" class="table table-striped">
            <thead>
              <tr>
                <td></td>
                <th>PO</th>
                <th>Origin</th>
                <th>Destination</th>
                <th>Cargo</th>
                <th>Driver</th>
                <th>Vehicle</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </form>
    </div>


    <!-- new -->
    <div id="page-ship-tracking" class="page">
      <div class="table-section">
        <table id="trackingTable" class="table table-striped">
          <thead>
            <tr>
              <th>Shipment</th>
              <th>Location</th>
              <th>Status</th>
              <th>Updated</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>

    <div id="page-archives" class="page">
      <div class="table-section">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>PO</th>
              <th>Origin</th>
              <th>Destination</th>
              <th>Cargo</th>
              <th>Driver</th>
              <th>Vehicle</th>
              <th>Status</th>
              <th>Created</th>
              <th>Action</th> <!-- Added Action column -->
            </tr>
          </thead>
          <tbody id="archiveTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>


  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="app.js"></script>
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
          window.location.href = "login/logout.php";
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