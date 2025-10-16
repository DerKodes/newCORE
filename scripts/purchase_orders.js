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

// // ---------- Purchase Orders (Core 1) ----------
// fetch("../api/purchase_orders.php")
//   .then((res) => res.json())
//   .then((data) => {
//     const tbody = document.querySelector("#poTable tbody");
//     if (!tbody) return;
//     tbody.innerHTML = "";

//     data.forEach((po) => {
//       tbody.innerHTML += `
//         <tr>
//           <td>${po.po_number}</td>
//           <td>${po.supplier}</td>
//           <td>${po.order_date}</td>
//           <td>${po.origin ?? ""}</td>
//           <td>${po.destination ?? ""}</td>
//           <td>${po.cargo_info ?? ""}</td>
//           <td class="align-middle">
//             <div class="btn-group btn-group-sm" role="group">
//               <button class="btn badge btn-success me-1">Edit</button>
//               <button class="btn badge btn-primary me-1">View</button>
//               <button class="btn badge btn-secondary">Archive</button>
//             </div>
//           </td>
//         </tr>`;
//     });
//   })
//   .catch((err) => console.error("Error fetching Purchase Orders:", err));

// ---------- Shipments (Core 3) ----------
// fetch("../api/core3_pull.php")
//   .then((res) => res.json())
//   .then((data) => {
//     const tbody = document.querySelector("#shipmentTable tbody");
//     if (!tbody) {
//       console.warn("No shipmentTable found in HTML.");
//       return;
//     }
//     tbody.innerHTML = "";

//     data.forEach((ship) => {
//       tbody.innerHTML += `
//         <tr>
//           <td>${ship.sender_name}</td>
//           <td>${ship.receiver_name}</td>
//           <td>${ship.address}</td>
//           <td>${ship.weight}</td>
//           <td>${ship.package_info}</td>
//           <td>${ship.status}</td>
//           <td>${ship.created_at}</td>
//           <td class="align-middle">
//             <div class="btn-group btn-group-sm" role="group">
//               <button class="btn badge btn-success me-1">Edit</button>
//               <button class="btn badge btn-primary me-1">View</button>
//               <button class="btn badge btn-secondary">Archive</button>
//             </div>
//           </td>


         
//         </tr>`;
//     });
//   })
//   .catch((err) => console.error("Error fetching Core 3 shipments:", err));
