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

// ---------- Archived Shipments ----------
async function loadArchives() {
  try {
    const response = await fetch("../api/shipment.php?action=archives");
    const data = await response.json();
    const tbody = document.getElementById("archiveTableBody");
    tbody.innerHTML = "";
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="10" class="text-center">No archived shipments found.</td></tr>`;
      return;
    }
    data.forEach((row) => {
      tbody.innerHTML += `
        <tr>
          <td>${row.id}</td>
          <td>${row.po_number}</td>
          <td>${row.origin}</td>
          <td>${row.destination}</td>
          <td>${row.cargo_info}</td>
          <td>${row.driver_name}</td>
          <td>${row.vehicle_number}</td>
          <td>${row.status}</td>
          <td>${row.created_at}</td>
          <td class="text-center">
            <button class="btn btn-sm btn-success me-2" onclick="restoreShipment(${row.id})">
              <i class="bi bi-arrow-counterclockwise"></i> Restore
            </button>
            <button class="btn btn-sm btn-danger" onclick="deleteShipment(${row.id})">
              <i class="bi bi-trash"></i> Delete
            </button>
          </td>
        </tr>`;
    });
  } catch (error) {
    console.error("Error fetching archives:", error);
    document.getElementById(
      "archiveTableBody"
    ).innerHTML = `<tr><td colspan="10" class="text-center text-danger">Error loading archives.</td></tr>`;
  }
}
loadArchives();

// ---------- Restore ----------
function restoreShipment(id) {
  Swal.fire({
    title: "Restore Shipment?",
    text: "This will move the shipment back to 'Delivered'.",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, restore it",
    cancelButtonText: "Cancel",
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`../api/shipment.php?action=restore&id=${id}`, { method: "POST" })
        .then((res) => res.json())
        .then((res) => {
          if (res.success) {
            Swal.fire("Restored!", res.message, "success");
            loadArchives();
          } else {
            Swal.fire("Error", res.message, "error");
          }
        })
        .catch(() => Swal.fire("Error", "Request failed.", "error"));
    }
  });
}

// ---------- Permanently Delete ----------
function deleteShipment(id) {
  Swal.fire({
    title: "Delete Permanently?",
    text: "This action cannot be undone!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, delete it",
    cancelButtonText: "Cancel",
    dangerMode: true,
  }).then((result) => {
    if (result.isConfirmed) {
      fetch(`../api/shipment.php?action=delete&id=${id}`, { method: "POST" })
        .then((res) => res.json())
        .then((res) => {
          if (res.success) {
            Swal.fire("Deleted!", res.message, "success");
            loadArchives();
          } else {
            Swal.fire("Error", res.message, "error");
          }
        })
        .catch(() => Swal.fire("Error", "Request failed.", "error"));
    }
  });
}
