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

// ================== API Fetch Logic ==================

// ---------- KPIs ----------
fetch("../api/dashboard_kpis.php")
  .then((res) => res.text())
  .then((text) => {
    try {
      const data = JSON.parse(text);
      document.getElementById("kpiShipments").innerText =
        data.active_shipments || 0;
      document.getElementById("kpiConsol").innerText =
        data.open_consolidations || 0;
      document.getElementById("kpiEvents").innerText =
        data.tracking_events || 0;
      document.getElementById("kpiPOs").innerText = data.linked_pos || 0;
    } catch (e) {
      console.error("Invalid JSON:", text);
    }
  })
  .catch((err) => console.error("KPI fetch failed:", err));

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

// ================== Notifications ==================
async function fetchNotifications() {
  try {
    const res = await fetch("../api/notifications.php");
    const data = await res.json();
    const list = document.getElementById("listNotifs");
    list.innerHTML = "";

    if (data.length === 0) {
      list.innerHTML = `<div class="list-group-item text-center text-muted">No notifications</div>`;
      return;
    }

    // Only keep the latest 3 notifications
    const latestThree = data.slice(0, 3);

    latestThree.forEach((n) => {
      list.innerHTML += `
        <div class="list-group-item">
          <div><strong>${n.title}</strong></div>
          <small class="text-muted">${n.message} • ${n.time}</small>
        </div>`;
    });
  } catch (err) {
    console.error("Error fetching notifications:", err);
  }
}

// ---------- Helpers ----------
function getStatusClass(status) {
  switch (status.toLowerCase()) {
    case "delivered":
      return "bg-success";
    case "in transit":
      return "bg-warning text-dark";
    case "delayed":
      return "bg-danger";
    default:
      return "bg-secondary";
  }
}

function getNotifType(type) {
  switch (type.toLowerCase()) {
    case "success":
      return "success";
    case "warning":
      return "warning";
    case "error":
      return "danger";
    default:
      return "info";
  }
}

// ---------- Init Recent + Notifs ----------
fetchRecentShipments();
fetchNotifications();

// Auto-refresh every 60 seconds
setInterval(() => {
  fetchRecentShipments();
  fetchNotifications();
}, 60000);
