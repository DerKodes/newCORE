<?php
require '../api/db.php';
$id = intval($_GET['id'] ?? 0);
if (!$id) { die("Missing id"); }
$stmt = $conn->prepare("SELECT b.*, s.id AS shipment_no, p.po_number, p.origin, p.destination, p.cargo_info
                        FROM bills_of_lading b
                        JOIN shipments s ON b.shipment_id = s.id
                        JOIN purchase_orders p ON s.po_id = p.id
                        WHERE b.id=? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$bl = $res->fetch_assoc();
$stmt->close();
if (!$bl) { die("BL not found"); }
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Print BL <?= htmlspecialchars($bl['bl_number']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #fff; }
    .bl-sheet { width: 210mm; margin: 0 auto; padding: 16mm; }
    .bl-box { border: 1px solid #000; padding: 8px; min-height: 38px; }
    .bl-label { font-size: 11px; text-transform: uppercase; color: #333; }
    .bl-value { font-size: 13px; font-weight: 600; white-space: pre-wrap; }
    .bl-title { font-weight: 800; font-size: 20px; letter-spacing: 1px; }
    .logo { height: 40px; }
    @media print {
      .no-print { display:none; }
      .bl-sheet { margin: 0; padding: 8mm; }
    }
  </style>
</head>
<body>
  <div class="no-print d-flex justify-content-center gap-2 p-2">
    <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Print</button>
    <a href="bl.php" class="btn btn-outline-secondary">Back</a>
  </div>
  <div class="bl-sheet">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="d-flex align-items-center gap-2">
        <img src="../assets/logo.png" class="logo" alt="SLATE">
        <div>
          <div class="bl-title">BILL OF LADING (<?= htmlspecialchars($bl['type']) ?>)</div>
          <div>BL No: <strong><?= htmlspecialchars($bl['bl_number']) ?></strong></div>
        </div>
      </div>
      <div class="text-end">
        <div>Issue Place: <strong><?= htmlspecialchars($bl['issue_place'] ?? '') ?></strong></div>
        <div>Issue Date: <strong><?= htmlspecialchars($bl['issue_date'] ?? '') ?></strong></div>
        <div>Status: <span class="badge bg-<?= ($bl['status']==='ISSUED'?'success':($bl['status']==='VOID'?'danger':'secondary')) ?>"><?= htmlspecialchars($bl['status']) ?></span></div>
      </div>
    </div>

    <div class="row g-2">
      <div class="col-6">
        <div class="bl-box">
          <div class="bl-label">Shipper</div>
          <div class="bl-value"><?= nl2br(htmlspecialchars($bl['shipper'])) ?></div>
        </div>
      </div>
      <div class="col-6">
        <div class="bl-box">
          <div class="bl-label">Consignee</div>
          <div class="bl-value"><?= nl2br(htmlspecialchars($bl['consignee'])) ?></div>
        </div>
      </div>
      <div class="col-6">
        <div class="bl-box">
          <div class="bl-label">Notify Party</div>
          <div class="bl-value"><?= nl2br(htmlspecialchars($bl['notify_party'])) ?></div>
        </div>
      </div>
      <div class="col-3">
        <div class="bl-box">
          <div class="bl-label">Port of Loading</div>
          <div class="bl-value"><?= htmlspecialchars($bl['port_of_loading']) ?></div>
        </div>
      </div>
      <div class="col-3">
        <div class="bl-box">
          <div class="bl-label">Port of Discharge</div>
          <div class="bl-value"><?= htmlspecialchars($bl['port_of_discharge']) ?></div>
        </div>
      </div>
      <div class="col-4">
        <div class="bl-box">
          <div class="bl-label">Vessel / Voyage No.</div>
          <div class="bl-value"><?= htmlspecialchars(($bl['vessel'] ?? '').' '.($bl['voyage_no'] ?? '')) ?></div>
        </div>
      </div>
      <div class="col-4">
        <div class="bl-box">
          <div class="bl-label">Place of Receipt</div>
          <div class="bl-value"><?= htmlspecialchars($bl['place_of_receipt'] ?? '') ?></div>
        </div>
      </div>
      <div class="col-4">
        <div class="bl-box">
          <div class="bl-label">Place of Delivery</div>
          <div class="bl-value"><?= htmlspecialchars($bl['place_of_delivery'] ?? '') ?></div>
        </div>
      </div>
      <div class="col-12">
        <div class="bl-box">
          <div class="bl-label">Marks & Numbers</div>
          <div class="bl-value"><?= nl2br(htmlspecialchars($bl['marks_numbers'] ?? '')) ?></div>
        </div>
      </div>
      <div class="col-12">
        <div class="bl-box">
          <div class="bl-label">Description of Goods</div>
          <div class="bl-value"><?= nl2br(htmlspecialchars($bl['description_of_goods'] ?? '')) ?></div>
        </div>
      </div>
      <div class="col-4">
        <div class="bl-box">
          <div class="bl-label">No. of Packages</div>
          <div class="bl-value"><?= htmlspecialchars($bl['packages'] ?? '') ?></div>
        </div>
      </div>
      <div class="col-4">
        <div class="bl-box">
          <div class="bl-label">Gross Weight</div>
          <div class="bl-value"><?= htmlspecialchars($bl['gross_weight'] ?? '') ?></div>
        </div>
      </div>
      <div class="col-4">
        <div class="bl-box">
          <div class="bl-label">Measurement</div>
          <div class="bl-value"><?= htmlspecialchars($bl['measurement'] ?? '') ?></div>
        </div>
      </div>
    </div>

    <div class="mt-4 d-flex justify-content-between">
      <div>
        <div class="bl-label">Shipment</div>
        <div class="bl-value">#<?= htmlspecialchars($bl['shipment_no']) ?> • PO <?= htmlspecialchars($bl['po_number']) ?> • <?= htmlspecialchars($bl['origin']) ?> → <?= htmlspecialchars($bl['destination']) ?></div>
      </div>
      <div class="text-end">
        <div class="bl-label">For and on behalf of</div>
        <div class="bl-value">SLATE Logistics</div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</body>
</html>
