<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shipment Tracking | CORE 1</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/style.css">

  <style>
    body {
      background-color: #f9f9f9;
    }

    .tracking-container {
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .progress-steps {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 30px 0;
      position: relative;
    }

    .progress-steps::before {
      content: "";
      position: absolute;
      top: 50%;
      left: 0;
      width: 100%;
      height: 4px;
      background: linear-gradient(to right,
          #dc3545 var(--progress-width, 0%),
          #ddd var(--progress-width, 0%));
      transform: translateY(-50%);
      z-index: 1;
    }

    .step {
      z-index: 2;
      background: #fff;
      border-radius: 50%;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 3px solid #ddd;
      color: #999;
      font-weight: bold;
    }

    .step.active {
      border-color: #dc3545;
      color: #dc3545;
      background: #fff;
    }

    .timeline-item {
      background: #fff;
      border-radius: 10px;
      padding: 12px 15px;
      margin-bottom: 15px;
      border-left: 4px solid #dc3545;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
    }

    .timeline-item strong {
      color: #dc3545;
    }

    .timeline-item .small {
      display: block;
      margin: 3px 0;
      color: #666;
    }

    .search-bar {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .search-bar input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    .search-bar button {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: bold;
    }

    .no-result {
      text-align: center;
      color: #888;
      margin-top: 30px;
    }
  </style>
</head>

<body>
  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <div class="logo"><img src="../assets/slate.png" alt="Logo"></div>
    <div class="system-name">CORE TRANSACTION 1</div>
    <a href="dashboard.php">Dashboard</a>
    <a href="purchase_orders.php">Purchase Orders</a>
    <a href="shipment.php">Shipment Booking</a>
    <a href="hmb.php">BL Generator</a>
    <a href="consolidation.php">Consolidation</a>
    <a href="ship_tracking.php" class="active">Tracking</a>
    <a href="archives.php">Archives</a>
  </div>

  <!-- Main Content -->

  <!-- Main Content -->
  <div class="content" id="content">
    <div class="header">
      <div class="hamburger" id="hamburger">☰</div>
      <h1 id="pageTitle">Tracking <span class="system-title">| CORE 1</span></h1>
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



    <div class="tracking">
      <div class="search-bar">
        <input type="text" id="searchTracking" placeholder="Enter PO Number..." />
        <button id="btnSearch">Search</button>
      </div>

      <div id="progressSection" class="text-center" style="display: none;">
        <div class="progress-steps">
          <div class="step" id="step1"><i class="bi bi-truck"></i></div>
          <div class="step" id="step2"><i class="bi bi-house-door"></i></div>
          <div class="step" id="step3"><i class="bi bi-check-circle"></i></div>
        </div>
        <div class="d-flex justify-content-between small text-muted">
          <span>Ready</span>
          <span>In Transit</span>
          <span>Delivered</span>
        </div>
      </div>

      <div id="timelineSection" class="timeline"></div>
      <div id="noResult" class="no-result">Enter a PO Number to begin tracking.</div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="../scripts/tracking.js"></script>
    <script src="../scripts/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      const searchInput = document.getElementById("searchTracking");
      const btnSearch = document.getElementById("btnSearch");
      const timeline = document.getElementById("timelineSection");
      const progressSection = document.getElementById("progressSection");
      const noResult = document.getElementById("noResult");

      btnSearch.addEventListener("click", () => searchShipment());
      searchInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") searchShipment();
      });

      async function searchShipment() {
        const po = searchInput.value.trim();
        if (po === "") {
          noResult.innerHTML = "Please enter a PO Number.";
          noResult.style.display = "block";
          progressSection.style.display = "none";
          timeline.innerHTML = "";
          return;
        }

        const res = await fetch(`../api/shipment.php?action=search&po_number=${encodeURIComponent(po)}`);
        const result = await res.json();

        if (!result.success || !result.data.length) {
          noResult.innerHTML = "No tracking data found for this PO.";
          noResult.style.display = "block";
          progressSection.style.display = "none";
          timeline.innerHTML = "";
          return;
        }

        const shipment = result.data[0];
        const status = shipment.status || "Ready";

        // Update progress bar
        progressSection.style.display = "block";
        noResult.style.display = "none";
        document.querySelectorAll(".step").forEach(step => step.classList.remove("active"));
        if (status === "Ready") document.getElementById("step1").classList.add("active");
        else if (status === "In Transit") document.getElementById("step2").classList.add("active");
        else if (status === "Delivered") document.getElementById("step3").classList.add("active");

        // Fetch tracking history
        const trackRes = await fetch(`../api/ship_tracking.php?shipment_id=${shipment.id}`);
        const trackData = await trackRes.json();

        timeline.innerHTML = "";
        if (trackData.length === 0) {
          timeline.innerHTML = `
    <div class="timeline-item">
      <strong>${shipment.status}</strong>
      <div class="small text-muted">${new Date().toLocaleString()}</div>
      <div>Origin: ${shipment.origin} → Destination: ${shipment.destination}</div>
    </div>
  `;
          return;
        }
        trackData.forEach(t => {
          timeline.innerHTML += `
          <div class="timeline-item">
            <strong>${t.status}</strong>
            <div class="small text-muted">${new Date(t.updated_at).toLocaleString()}</div>
            <div>City: ${t.location}</div>
          </div>
        `;
        });
      }
    </script>
</body>

</html>