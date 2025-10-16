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
  header("Location: ../login/login.php");
  exit();
}
?>
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



        <!-- Page Content -->




    </div>


    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
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