const sidebar = document.getElementById("sidebar");
const backdrop = document.getElementById("sidebar-backdrop");
const toggleBtn = document.getElementById("sidebarToggle");
const profileModal = document.querySelector("[data-profile-modal]");
const profileTriggers = document.querySelectorAll("[data-profile-trigger]");
const profileCloseButtons = document.querySelectorAll("[data-profile-close]");
const profileOverlay = profileModal?.querySelector("[data-profile-overlay]");

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

function openProfileModal() {
  if (!profileModal) return;
  profileModal.classList.remove("hidden");
  profileModal.classList.add("flex");
  document.body.classList.add("overflow-hidden");
  closeSidebar();
}

function closeProfileModal() {
  if (!profileModal) return;
  profileModal.classList.add("hidden");
  profileModal.classList.remove("flex");
  document.body.classList.remove("overflow-hidden");
}

profileTriggers.forEach((trigger) => {
  trigger.addEventListener("click", (event) => {
    event.preventDefault();
    openProfileModal();
  });
});

profileCloseButtons.forEach((button) => {
  button.addEventListener("click", closeProfileModal);
});

profileOverlay?.addEventListener("click", closeProfileModal);

document.addEventListener("keydown", (event) => {
  if (event.key !== "Escape") return;

  if (profileModal && !profileModal.classList.contains("hidden")) {
    closeProfileModal();
    return;
  }

  closeSidebar();
});

if (profileModal?.dataset.openOnload === "1") {
  openProfileModal();
}

const liveDate = document.getElementById("live-date");
if (liveDate) {
  const now = new Date();
  liveDate.textContent = now.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric"
  });
}
