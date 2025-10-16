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

// ================== Recent Shipments ==================
async function fetchRecentShipments() {
  try {
    const res = await fetch("../api/recent_shipments.php");
    if (!res.ok) throw new Error("Network response was not ok");

    const data = await res.json();
    const tbody = document.getElementById("recentShipments");
    tbody.innerHTML = "";

    if (!Array.isArray(data) || data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center">No recent shipments found.</td></tr>`;
      return;
    }

    // Show latest 5 shipments only (already limited in API, but safe)
    const latestFive = data.slice(0, 5);

    latestFive.forEach((s) => {
      tbody.innerHTML += `
    <tr>
      <td>${s.shipping_ref}</td>
      <td>${s.supplier}</td>
      <td>${s.order_date ?? "N/A"}</td>
      <!-- Status with dynamic badge color -->
      <td>
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
      </td>
      <td>${s.origin}</td>
      <td>${s.destination}</td>
      <td>${s.vehicle_number ?? "N/A"}</td>
      <td>${s.created_at}</td>
    </tr>`;
    });
  } catch (err) {
    console.error("Error fetching recent shipments:", err);
    const tbody = document.getElementById("recentShipments");
    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error fetching shipments.</td></tr>`;
  }
}

// Call the function on page load
document.addEventListener("DOMContentLoaded", () => {
  fetchRecentShipments();
});
