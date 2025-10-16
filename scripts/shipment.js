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

// Run on page load
document.addEventListener("DOMContentLoaded", activateSidebar);

// ---------- Shipments CRUD ----------
// Archive a shipment
async function archiveShipment(id) {
  Swal.fire({
    title: "Are you sure?",
    text: "This shipment will be archived.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#6c757d",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Yes, archive it!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append("id", id);

      const res = await fetch("../api/shipment.php?action=archive", {
        method: "POST",
        body: formData,
      });
      const result = await res.json();

      if (result.success) {
        Swal.fire({
          title: "Archived!",
          text: result.message,
          icon: "success",
          timer: 1500,
          showConfirmButton: false,
        });
        fetchShipments();
      } else {
        Swal.fire("Error", result.message, "error");
      }
    }
  });
}

// Update shipment status
async function updateStatus(id, status, consolidated = 0) {
  if (status === "In Transit" && consolidated != 1) {
    Swal.fire({
      title: "Not Allowed",
      text: "This shipment must be consolidated before it can be set to In Transit.",
      icon: "warning",
      confirmButtonText: "OK",
    });
    return;
  }

  Swal.fire({
    title: `Set status to "${status}"?`,
    text: "This action cannot be undone.",
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#198754",
    cancelButtonColor: "#d33",
    confirmButtonText: `Yes, set to ${status}`,
  }).then(async (result) => {
    if (result.isConfirmed) {
      const formData = new FormData();
      formData.append("id", id);
      formData.append("status", status);

      const res = await fetch("../api/shipment.php?action=updateStatus", {
        method: "POST",
        body: formData,
      });
      const result = await res.json();

      if (result.success) {
        Swal.fire({
          title: "Updated!",
          text: `Shipment is now "${status}".`,
          icon: "success",
          timer: 1500,
          showConfirmButton: false,
        });
        fetchShipments();
      } else {
        Swal.fire("Error", result.message, "error");
      }
    }
  });
}

// Fetch Purchase Orders for modal select
async function fetchPOs() {
  const res = await fetch("../api/purchase_orders.php?action=list");
  const data = await res.json();
  const poSelect = document.getElementById("poSelect");
  poSelect.innerHTML = '<option value="">Select...</option>';
  data.forEach((po) => {
    poSelect.innerHTML += `<option value="${po.id}">${po.po_number} (${po.origin} → ${po.destination})</option>`;
  });
}

// Fetch all shipments and render table
async function fetchShipments() {
  const res = await fetch("../api/shipment.php?action=list");
  const data = await res.json();
  const tbody = document.getElementById("shipmentTable").querySelector("tbody");
  tbody.innerHTML = "";

  data.forEach((s) => {
    tbody.innerHTML += `
    <tr class="text-center align-middle">
      <td>${s.po_number}</td>
      <td>${s.origin}</td>
      <td>${s.destination}</td>
      <td>${s.cargo_info ?? ""}</td>
      <td>${s.driver_name ?? ""}</td>
      <td>${s.vehicle_number ?? ""}</td>

      <!-- Status column with dynamic badge -->
      <td>
        <h6>
          <span class="badge ${
            s.status === "Ready"
              ? "bg-info"
              : s.status === "In Transit"
              ? "bg-warning"
              : s.status === "Delivered"
              ? "bg-success"
              : "bg-secondary"
          }">
            ${s.status}
          </span>
        </h6>
      </td>

      <!-- Consolidated column -->
      <td class="text-center align-middle">
        
          <span class="badge ${
            s.consolidated == 1 ? "bg-success" : "bg-danger"
          }">
            ${s.consolidated == 1 ? "Yes" : "No"}
          </span>
        
      </td>

      <!-- Action buttons column -->
<td class="text-center align-middle">
  ${
    s.status === "Ready" && s.consolidated == 1
      ? `<button class="btn badge btn-sm btn-warning me-1" 
           onclick="updateStatus(${s.id}, 'In Transit', ${s.consolidated})">
           Set Transit
         </button>`
      : ""
  }
  ${
    s.status === "In Transit"
      ? `<button class="btn badge btn-sm btn-success me-1" 
           onclick="updateStatus(${s.id}, 'Delivered', ${s.consolidated})">
           Set Delivered
         </button>`
      : ""
  }
  ${
    s.status === "Delivered"
      ? `<button class="btn badge btn-sm btn-secondary" 
           onclick="archiveShipment(${s.id})">
           Archive
         </button>`
      : ""
  }
</td>



    </tr>
  `;
  });
}

// ----------- Search Shipment by PO Number -----------
document
  .getElementById("searchShipment")
  ?.addEventListener("input", async (e) => {
    const query = e.target.value.trim();

    if (query === "") {
      fetchShipments(); // reset list
      return;
    }

    const res = await fetch(
      `../api/shipment.php?action=search&po_number=${encodeURIComponent(query)}`
    );
    const result = await res.json();

    const tbody = document.querySelector("#shipmentTable tbody");
    tbody.innerHTML = "";

    if (!result.data || result.data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="9" class="text-center">No shipments found for "${query}".</td></tr>`;
      return;
    }

    result.data.forEach((s) => {
      tbody.innerHTML += `
      <tr class="text-center align-middle">
        <td>${s.po_number}</td>
        <td>${s.origin}</td>
        <td>${s.destination}</td>
        <td>${s.cargo_info ?? ""}</td>
        <td>${s.driver_name ?? ""}</td>
        <td>${s.vehicle_number ?? ""}</td>
        <td><span class="badge ${
          s.status === "Ready"
            ? "bg-info"
            : s.status === "In Transit"
            ? "bg-warning"
            : "bg-success"
        }">${s.status}</span></td>
        <td><span class="badge ${
          s.consolidated == 1 ? "bg-success" : "bg-danger"
        }">${s.consolidated == 1 ? "Yes" : "No"}</span></td>
        <td><button class="btn btn-sm btn-secondary" onclick="archiveShipment(${
          s.id
        })">Archive</button></td>
      </tr>`;
    });
  });

// Handle shipment form submission (modal)
document
  .getElementById("shipmentForm")
  .addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    const res = await fetch("../api/shipment.php?action=create", {
      method: "POST",
      body: formData,
    });

    const result = await res.json();

    if (result.success) {
      Swal.fire({
        title: "Shipment Created!",
        text: result.message,
        icon: "success",
        timer: 2000,
        showConfirmButton: false,
      });

      this.reset(); // clear form
      fetchShipments(); // reload table

      // Close modal (Bootstrap 5)
      const shipmentModal = bootstrap.Modal.getInstance(
        document.getElementById("shipmentModal")
      );
      shipmentModal.hide();
    } else {
      Swal.fire({
        title: "Error",
        text: result.message,
        icon: "error",
      });
    }
  });
// Initialize shipment booking data
fetchPOs();
fetchShipments();
