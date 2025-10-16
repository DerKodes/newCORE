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

// ---------- Fetch and render consolidation data ----------
async function fetchConsolidation() {
  const res = await fetch("../api/consolidation.php?action=list");
  const data = await res.json();
  const tbody = document.querySelector("#suggestedTable tbody");
  tbody.innerHTML = "";

  if (data.length === 0) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center">No shipments available for consolidation</td></tr>`;
    return;
  }

  data.forEach((s) => {
    tbody.innerHTML += `
      <tr class="text-center align-middle">
        <td><input type="checkbox" name="shipment_ids[]" value="${s.id}"></td>
        <td>${s.po_number}</td>
        <td>${s.origin}</td>
        <td>${s.destination}</td>
        <td>${s.cargo_details ?? ""}</td>
        <td>${s.driver_name ?? ""}</td>
        <td>${s.vehicle_number ?? ""}</td>
        <td><h6><span class="badge bg-primary">${s.status}</span></h6></td>
        <td>${s.created_at}</td>
        <td>
          <button type="button" class="btn badge btn-warning btn-sm" onclick="editConsolidation(${
            s.id
          })">Edit</button>
        </td>
      </tr>`;
  });
}

// ---------- Create consolidation ----------
document
  .getElementById("consolidationForm")
  ?.addEventListener("submit", async function (e) {
    e.preventDefault();

    // Collect selected shipment IDs
    const shipment_ids = [];
    document
      .querySelectorAll('input[name="shipment_ids[]"]:checked')
      .forEach((cb) => {
        shipment_ids.push(cb.value);
      });

    if (shipment_ids.length === 0) {
      Swal.fire({
        icon: "warning",
        title: "No shipments selected",
        text: "⚠️ Please select at least one shipment.",
      });
      return;
    }

    // Send to API
    const formData = new FormData();
    shipment_ids.forEach((id) => formData.append("shipment_ids[]", id));

    try {
      const res = await fetch("../api/consolidation.php?action=create", {
        method: "POST",
        body: formData,
      });
      const result = await res.json();

      if (result.success) {
        Swal.fire({
          title: "Consolidation Created!",
          text: result.message,
          icon: "success",
          timer: 2000,
          showConfirmButton: false,
        });
        fetchConsolidation(); // refresh table
        this.reset(); // reset form
      } else {
        Swal.fire({
          icon: "error",
          title: "Failed",
          text: result.message,
        });
      }
    } catch (err) {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "❌ Failed to create consolidation. Try again.",
      });
    }
  });


// ---------- Initial load ----------
fetchConsolidation();
