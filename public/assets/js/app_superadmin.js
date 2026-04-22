const sidebar = document.getElementById("sidebar");
const backdrop = document.getElementById("sidebar-backdrop");
const toggleBtn = document.getElementById("sidebarToggle");

function openSidebar() {
  sidebar?.classList.remove("-translate-x-full");
  backdrop?.classList.remove("hidden");
}

function closeSidebar() {
  sidebar?.classList.add("-translate-x-full");
  backdrop?.classList.add("hidden");
}

function syncSidebar() {
  if (!sidebar || !backdrop) return;

  if (window.innerWidth >= 1024) {
    sidebar.classList.remove("-translate-x-full");
    backdrop.classList.add("hidden");
  } else {
    sidebar.classList.add("-translate-x-full");
  }
}

window.addEventListener("resize", syncSidebar);
syncSidebar();

toggleBtn?.addEventListener("click", () => {
  if (!sidebar) return;
  const isClosed = sidebar.classList.contains("-translate-x-full");
  if (isClosed) {
    openSidebar();
  } else {
    closeSidebar();
  }
});

backdrop?.addEventListener("click", closeSidebar);
document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeSidebar();
  }
});

const liveDate = document.getElementById("live-date");
if (liveDate) {
  const now = new Date();
  liveDate.textContent = now.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}

