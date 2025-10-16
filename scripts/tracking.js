// ======================= UI + Theme =======================
document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("sidebar");
  const content = document.getElementById("content");
  const hamburger = document.getElementById("hamburger");
  const themeToggle = document.getElementById("themeToggle");

  function setTheme(dark) {
    document.body.classList.toggle("dark-mode", dark);
    themeToggle.checked = dark;
    localStorage.setItem("core1_theme", dark ? "dark" : "light");
  }

  const saved = (localStorage.getItem("core1_theme") || "").toLowerCase();
  setTheme(saved === "dark");
  themeToggle.addEventListener("change", () => setTheme(themeToggle.checked));
  hamburger.addEventListener("click", () => {
    sidebar.classList.toggle("collapsed");
    content.classList.toggle("expanded");
  });

  // ======================= Tracking Search =======================
  const searchInput = document.getElementById("searchTracking");
  const btnSearch = document.getElementById("btnSearch");
  const progressSteps = document.querySelectorAll(".step");
  const timeline = document.getElementById("timelineSection");
  const noResult = document.getElementById("noResult");
  const progressSection = document.getElementById("progressSection");

  let isSearching = false;

  btnSearch.addEventListener("click", () => {
    if (!isSearching) searchShipment();
  });

  searchInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter" && !isSearching) {
      searchShipment();
    }
  });

  async function searchShipment() {
    const po = searchInput.value.trim();
    if (po === "") {
      showMessage("Please enter a PO Number.");
      return;
    }

    isSearching = true;
    btnSearch.disabled = true;
    btnSearch.textContent = "Searching...";

    try {
      // 🔹 Fetch shipment details
      const res = await fetch(
        `../api/shipment.php?action=search&po_number=${encodeURIComponent(po)}`
      );
      const result = await res.json();

      if (!result.success || !result.data.length) {
        showMessage("No tracking data found for this PO Number.");
        resetSearchUI();
        return;
      }

      const shipment = result.data[0];
      updateProgress(shipment.shipment_status || "Ready");
      progressSection.style.display = "block";
      noResult.style.display = "none";

      // 🔹 Fetch tracking history
      const trackingRes = await fetch(
        `../api/ship_tracking.php?shipment_id=${shipment.shipment_id}`
      );
      const trackingResult = await trackingRes.json();
      const trackingData = trackingResult.data || trackingResult;

      timeline.innerHTML = "";

      if (!trackingData || trackingData.length === 0) {
        timeline.innerHTML = `
          <div class="timeline-item mb-4  p-2">
            <div><strong>${
              shipment.shipment_status || "To Be Track"
            }</strong></div>
            <div class="small text-muted">${new Date().toLocaleString()}</div>
            <div>Route: ${shipment.origin} → ${shipment.destination}</div>
          </div>`;
      } else {
        trackingData.forEach((t) => {
          let displayLocation = t.location;

          // 🧠 Replace “In Transit” placeholder location with readable label
          if (
            !displayLocation ||
            displayLocation.toLowerCase().includes("transit") ||
            displayLocation.trim() === ""
          ) {
            displayLocation = "On the way to destination";
          }

          // 🧩 Build timeline entry with conditional City line
          let cityLine = "";
          if (t.status.toLowerCase() !== "in transit") {
            cityLine = `<div>City: ${displayLocation}</div>`;
          }

          timeline.innerHTML += `
  <div class="timeline-item mb-3 p-3">
    <div><strong>${t.status}</strong></div>
    <div class="small text-muted">${new Date(
      t.updated_at
    ).toLocaleString()}</div>
    ${cityLine}
  </div>`;
        });
      }
    } catch (err) {
      console.error("Error fetching data:", err);
      showMessage("⚠️ Connection error. Try again later.");
    } finally {
      resetSearchUI();
    }
  }

  // ======================= Helper Functions =======================
  function updateProgress(status) {
    clearProgress();
    const step1 = document.getElementById("step1");
    const step2 = document.getElementById("step2");
    const step3 = document.getElementById("step3");

    const s = (status || "").toLowerCase();

    if (s === "ready") {
      step1.classList.add("active");
      setProgressLine(1);
    } else if (s === "in transit") {
      step1.classList.add("active");
      step2.classList.add("active");
      setProgressLine(2);
    } else if (s === "delivered") {
      step1.classList.add("active");
      step2.classList.add("active");
      step3.classList.add("active");
      setProgressLine(3);
    }
  }

  // Color the progress line up to active steps
  function setProgressLine(activeCount) {
    const line = document.querySelector(".progress-steps::before");
    progressSteps.forEach((step, index) => {
      step.style.borderColor = index < activeCount ? "#dc3545" : "#ddd";
      step.style.color = index < activeCount ? "#dc3545" : "#999";
    });
    const progressBar = document.querySelector(".progress-steps");
    progressBar.style.setProperty(
      "--progress-width",
      `${(activeCount - 1) * 50}%`
    );
  }

  function clearProgress() {
    progressSteps.forEach((step) => step.classList.remove("active"));
  }

  function showMessage(msg) {
    noResult.textContent = msg;
    noResult.style.display = "block";
    progressSection.style.display = "none";
    timeline.innerHTML = "";
    clearProgress();
  }

  function resetSearchUI() {
    isSearching = false;
    btnSearch.disabled = false;
    btnSearch.textContent = "Search";
  }
});
