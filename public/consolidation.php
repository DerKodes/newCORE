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

    <div id="page-consolidation">
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




  </div>


  <!-- JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="../scripts/consolidation.js"></script>
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