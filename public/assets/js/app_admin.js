
    // Sidebar toggle logic
const sidebar = document.getElementById("sidebar");
const backdrop = document.getElementById("sidebar-backdrop");
const toggleBtn = document.getElementById("sidebarToggle");

function openSidebar() {
  sidebar.classList.remove("-translate-x-full");
  backdrop.classList.remove("hidden");
}

function closeSidebar() {
  sidebar.classList.add("-translate-x-full");
  backdrop.classList.add("hidden");
}

function syncSidebar() {
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
  const isClosed = sidebar.classList.contains("-translate-x-full");
  isClosed ? openSidebar() : closeSidebar();
});

backdrop?.addEventListener("click", closeSidebar);
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") closeSidebar();
});




// Inventory status badge logic
function updateBadgeColor(select, id) {
    const badge = document.getElementById("badge-" + id);
    if (!badge) return;

    // Remove ALL possible color classes first
    badge.classList.remove(
        "bg-emerald-600", "bg-amber-400", "bg-red-600", "bg-gray-500",
        "text-white", "text-black"
    );

    if (select.value === "Available") {
        badge.classList.add("bg-emerald-600", "text-white");
        badge.textContent = "Available";
    }

    if (select.value === "Low Stock") {
        badge.classList.add("bg-amber-400", "text-black");
        badge.textContent = "Low Stock";
    }

    if (select.value === "Out of Stock") {
        badge.classList.add("bg-red-600", "text-white");
        badge.textContent = "Out of Stock";
    }
}




    //live date script
function updateDate() {
    const now = new Date();

    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };

    document.getElementById("live-date").textContent =
        now.toLocaleDateString('en-US', options);
}

    updateDate();



let currentPage = 1;
const loadMoreBtn = document.getElementById("loadMoreBtn");

if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", function() {
        currentPage++;

        fetch(`/petron_system/public/admin/ajax/load_delivery_history.php?page=` + currentPage)
            .then(response => response.text())
            .then(data => {
                if (data.trim() === "") {
                    this.innerText = "No more records";
                    this.disabled = true;
                    return;
                }

                document.getElementById("delivery-table-body")
                    ?.insertAdjacentHTML("beforeend", data);
            });
    });
}
