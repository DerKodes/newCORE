// ======================= UI: Sidebar, Nav, Dark Mode =======================
const sidebar = document.getElementById("sidebar");
const content = document.getElementById("content");
const hamburger = document.getElementById("hamburger");
const themeToggle = document.getElementById("themeToggle");

function setTheme(dark) {
  document.body.classList.toggle("dark-mode", dark);
  themeToggle.checked = dark;
  localStorage.setItem("core1_theme", dark ? "dark" : "light");
}

// Init theme
(() => {
  const saved = (localStorage.getItem("core1_theme") || "").toLowerCase();
  setTheme(saved === "dark");
})();
themeToggle.addEventListener("change", () => setTheme(themeToggle.checked));

hamburger.addEventListener("click", () => {
  sidebar.classList.toggle("collapsed");
  content.classList.toggle("expanded");
});

// Highlight active sidebar link
function activateSidebar() {
  const currentPage = window.location.pathname.split("/").pop();

  document.querySelectorAll(".sidebar a, .module-nav a").forEach((a) => {
    a.classList.remove("active");
  });

  document
    .querySelectorAll(
      `.sidebar a[href="${currentPage}"], .module-nav a[href="${currentPage}"]`
    )
    .forEach((a) => a.classList.add("active"));

  const activeLink = document.querySelector(
    `.sidebar a[href="${currentPage}"]`
  );
  const titleEl = document.getElementById("pageTitle");
  if (activeLink && titleEl) {
    titleEl.innerHTML = `${activeLink.textContent.trim()} <span class="system-title">| CORE 1</span>`;
  }
}
document.addEventListener("DOMContentLoaded", activateSidebar);

// ================== HMB / BL Generator ==================
let hmbList = JSON.parse(localStorage.getItem("hmbList")) || [];
let shipmentCache = {}; // cache shipments for auto-fill

function saveHMBs() {
  localStorage.setItem("hmbList", JSON.stringify(hmbList));
}

// --------- Fetch Shipments for Dropdown ---------
async function fetchShipmentsForBL() {
  try {
    const res = await fetch("../api/shipment.php?action=list");
    const data = await res.json();
    const sel = document.getElementById("shipmentSelect");
    sel.innerHTML = '<option value="">Select a shipment...</option>';

    data.forEach((s) => {
      if (s.consolidated != 1) return; // only consolidated
      const alreadyUsed = hmbList.some((bl) => bl.shipment_id == s.id);
      if (alreadyUsed) return;

      shipmentCache[s.id] = s;

      const opt = document.createElement("option");
      opt.value = s.id;
      opt.textContent = `#${s.id} • PO ${s.po_number} • ${s.origin} → ${s.destination}`;
      sel.appendChild(opt);
    });
  } catch (err) {
    console.error("Error fetching shipments for BL:", err);
  }
}

// --------- Auto BL Number + Auto-fill ---------
function updateBLNumberPreview() {
  const shipmentId = document.getElementById("shipmentSelect").value;
  const type = document.getElementById("blType").value.toUpperCase();
  const blNumberField = document.getElementById("blNumber");
  const consigneeField = document.getElementById("blConsignee");
  const shipperField = document.getElementById("blShipper");

  if (!shipmentId || !type) {
    blNumberField.value = "";
    consigneeField.value = "";
    shipperField.value = "";
    return;
  }

  const year = new Date().getFullYear();
  const prefix = type === "HBL" ? "HBL" : "MBL";
  blNumberField.value = `${prefix}-${year}-${shipmentId
    .toString()
    .padStart(4, "0")}`;

  const shipment = shipmentCache[shipmentId];
  if (shipment) {
    consigneeField.value = shipment.destination || "N/A";
    shipperField.value = shipment.origin || "N/A";
  }
}
document
  .getElementById("shipmentSelect")
  ?.addEventListener("change", updateBLNumberPreview);
document
  .getElementById("blType")
  ?.addEventListener("input", updateBLNumberPreview);

// --------- Render BL Table ---------
function renderHMBs() {
  const tbody = document.querySelector("#blTable tbody");
  tbody.innerHTML = "";

  if (hmbList.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center">No BLs generated.</td></tr>`;
    return;
  }

  hmbList.forEach((bl, idx) => {
    const tr = document.createElement("tr");
    tr.innerHTML = `
      <td>${bl.bl_number}</td>
      <td>${bl.type}</td>
      <td>#${bl.shipment_id}</td>
      <td>${bl.consignee || "N/A"}</td>
      <td>${bl.shipper || "N/A"}</td>
      <td>${bl.created_at}</td>
      <td><span class="badge bg-success">ISSUED</span></td>
      <td>
        <div class="btn-group btn-group-sm" role="group">
          <button class="btn btn-sm badge btn-info previewBl me-1" data-bl='${JSON.stringify(
            bl
          )}'>Preview</button>
          <button class="btn btn-sm badge btn-success downloadPdf me-1" data-bl='${JSON.stringify(
            bl
          )}'>Download PDF</button>
          <button class="btn btn-sm badge btn-secondary deleteBl" data-idx="${idx}">Archive</button>
        </div>
      </td>`;
    tbody.appendChild(tr);
  });

  // Preview listener
  document.querySelectorAll(".previewBl").forEach((btn) => {
    btn.addEventListener("click", () => {
      const bl = JSON.parse(btn.dataset.bl);
      const printWindow = window.open("", "_blank", "width=500,height=600");
      printWindow.document.write(`
      <html>
        <head>
          <title>Preview BL</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 20px; }
            #blSticker { width: 400px; border: 2px solid black; padding: 15px; margin: auto; }
            #blSticker h4 { text-align: center; font-weight: bold; margin-bottom: 10px; }
            #blSticker p { margin: 5px 0; font-size: 14px; }
            #blSticker hr { margin: 10px 0; border: 1px solid #000; }
            #qrCodePreview { text-align:center; margin:10px 0; }
            #blSticker .footer { text-align: center; margin-top: 10px; font-size: 13px; font-weight: bold; }
          </style>
        </head>
        <body>
          <div id="blSticker">
            <h4>BILL OF LADING</h4>
            <hr>
            <p><strong>BL Number:</strong> ${bl.bl_number}</p>
            <p><strong>Type:</strong> ${bl.type}</p>
            <p><strong>Shipment:</strong> #${bl.shipment_id}</p>
            <p><strong>Consignee:</strong> ${bl.consignee || "N/A"}</p>
            <p><strong>Shipper:</strong> ${bl.shipper || "N/A"}</p>
            <p><strong>Origin → Destination:</strong> ${
              bl.origin || "Origin"
            } → ${bl.destination || "Destination"}</p>
            <p><strong>Issue Date:</strong> ${bl.created_at}</p>
            <div id="qrCodePreview"></div>
            <hr>
            <p class="footer">📦 CORE 1 Freight Management</p>
          </div>
          <div style="text-align:center; margin-top:20px;">
            <button onclick="window.print()">Print</button>
            <button onclick="window.close()">Close</button>
          </div>
          <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
          <script>
            new QRCode(document.getElementById("qrCodePreview"), {
              text: "${bl.bl_number}",
              width: 100,
              height: 100
            });
          </script>
        </body>
      </html>
    `);
      printWindow.document.close();
    });
  });

  // PDF listener
  document.querySelectorAll(".downloadPdf").forEach((btn) => {
    btn.addEventListener("click", () =>
      generatePDF(JSON.parse(btn.dataset.bl))
    );
  });

  // Delete (Archive) listener
  document.querySelectorAll(".deleteBl").forEach((btn) => {
    btn.addEventListener("click", () => {
      const idx = btn.dataset.idx;
      const bl = hmbList[idx];

      Swal.fire({
        title: "Archive BL?",
        text: "This BL will be moved to the Archives.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, archive it!",
      }).then(async (result) => {
        if (result.isConfirmed) {
          try {
            const res = await fetch("../api/shipment.php?action=archiveBL", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(bl),
            });
            const resp = await res.json();

            if (resp.success) {
              hmbList.splice(idx, 1);
              saveHMBs();
              renderHMBs();

              Swal.fire({
                title: "Archived!",
                text: "The BL has been moved to Archives.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false,
              });
            } else {
              Swal.fire(
                "Error",
                resp.message || "Failed to archive BL.",
                "error"
              );
            }
          } catch (err) {
            console.error("Archive error:", err);
            Swal.fire(
              "Error",
              "Something went wrong while archiving.",
              "error"
            );
          }
        }
      });
    });
  });
}

// --------- Create BL / HMB ---------
document.getElementById("createForm")?.addEventListener("submit", (e) => {
  e.preventDefault();

  const shipmentId = document.getElementById("shipmentSelect").value;
  const type = document.getElementById("blType").value.toUpperCase();
  const blNumber = document.getElementById("blNumber").value;
  const consignee = document.getElementById("blConsignee").value;
  const shipper = document.getElementById("blShipper").value;

  if (!shipmentId || !type || !blNumber) {
    Swal.fire({
      title: "⚠️ Missing Information",
      text: "Please select a shipment and enter BL type.",
      icon: "warning",
      confirmButtonText: "OK",
    });
    return;
  }

  const newBL = {
    bl_number: blNumber,
    type,
    shipment_id: shipmentId,
    consignee,
    shipper,
    created_at: new Date().toLocaleString(),
    status: "ISSUED",
  };

  hmbList.push(newBL);
  saveHMBs();
  renderHMBs();

  Swal.fire({
    title: "✅ BL Generated",
    html: `<b>${blNumber}</b> has been created successfully.`,
    icon: "success",
    timer: 2000,
    showConfirmButton: false,
  });

  e.target.reset();
  document.getElementById("blNumber").value = "";
});

// --------- Generate PDF ---------
function generatePDF(bl) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  doc.setFontSize(18);
  doc.text("House / Master Bill of Lading", 20, 20);
  doc.setFontSize(14);
  doc.text(`BL Number: ${bl.bl_number}`, 20, 30);
  doc.text(`Type: ${bl.type}`, 20, 40);
  doc.text(`Shipment: #${bl.shipment_id}`, 20, 50);
  doc.text(`Shipper: ${bl.shipper}`, 20, 60);
  doc.text(`Consignee: ${bl.consignee}`, 20, 70);
  doc.text(`Created: ${bl.created_at}`, 20, 80);
  doc.text(`Status: ${bl.status}`, 20, 90);

  doc.save(`${bl.bl_number}.pdf`);
}

// --------- Push BL to Core 2 (Tariff) ---------
async function pushBLToCore2(bl) {
  try {
    const res = await fetch("http://core2.slatefreight-ph.com/api/tariffs.php?action=receiveBL", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(bl),
    });

    const data = await res.json();
    if (data.success) {
      console.log("✅ BL pushed to Core 2 Tariff:", data);
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'BL pushed to Core 2 Tariff',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true
      });
    } else {
      console.warn("⚠️ Core 2 rejected BL:", data.message);
      Swal.fire("Warning", data.message || "BL not accepted by Core 2", "warning");
    }
  } catch (err) {
    console.error("❌ Error pushing BL to Core 2:", err);
    Swal.fire("Error", "Could not connect to Core 2 Tariff API", "error");
  }
}


// --------- Init ---------
document.addEventListener("DOMContentLoaded", () => {
  fetchShipmentsForBL();
  renderHMBs();
});
