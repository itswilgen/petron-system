document.addEventListener("DOMContentLoaded", function () {
  const tableBody = document.getElementById("inventoryTableBody");
  const syncLabel = document.getElementById("inventoryLiveSyncText");
  const AUTO_SYNC_MS = 5000;

  if (!tableBody) {
    return;
  }

  let isSyncing = false;

  function formatNumber(value) {
    return Number(value || 0).toFixed(2);
  }

  function updateBadgeStatus(badge, status) {
    if (!badge) return;

    badge.classList.remove(
      "bg-emerald-600",
      "bg-amber-400",
      "bg-red-600",
      "bg-gray-500",
      "text-white",
      "text-black"
    );

    if (status === "Low Stock") {
      badge.classList.add("bg-amber-400", "text-black");
      badge.textContent = "Low Stock";
      return;
    }

    if (status === "Out of Stock") {
      badge.classList.add("bg-red-600", "text-white");
      badge.textContent = "Out of Stock";
      return;
    }

    if (status === "Available") {
      badge.classList.add("bg-emerald-600", "text-white");
      badge.textContent = "Available";
      return;
    }

    badge.classList.add("bg-gray-500", "text-white");
    badge.textContent = status || "-";
  }

  function updateSyncLabel(prefix) {
    if (!syncLabel) return;

    const timeText = new Date().toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit"
    });

    syncLabel.innerHTML = `
      <i class="fa-solid fa-rotate"></i>
      ${prefix}: ${timeText}
    `;
  }

  function getFuelRowMap(fuels) {
    const map = new Map();
    (fuels || []).forEach((fuel) => {
      map.set(String(fuel.id), fuel);
    });
    return map;
  }

  async function refreshInventoryFromServer() {
    if (isSyncing || document.hidden) {
      return;
    }

    isSyncing = true;

    try {
      const response = await fetch("/public/admin/ajax/inventory_fuels.php", {
        cache: "no-store"
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      if (!payload.success || !Array.isArray(payload.fuels)) {
        return;
      }

      const fuelMap = getFuelRowMap(payload.fuels);
      const rows = tableBody.querySelectorAll("tr[data-fuel-id]");

      rows.forEach((row) => {
        const rowId = row.getAttribute("data-fuel-id");
        const fuelData = fuelMap.get(String(rowId));
        if (!fuelData) return;

        const litersInput = row.querySelector(".inventory-liters-input");
        const priceInput = row.querySelector(".inventory-price-input");
        const statusSelect = row.querySelector(".inventory-status-select");
        const statusBadge = row.querySelector(".inventory-status-badge");

        if (litersInput) {
          litersInput.value = formatNumber(fuelData.liters);
        }

        if (priceInput && document.activeElement !== priceInput) {
          priceInput.value = formatNumber(fuelData.price);
        }

        if (statusSelect && document.activeElement !== statusSelect) {
          statusSelect.value = String(fuelData.status || "Available");
        }

        updateBadgeStatus(statusBadge, String(fuelData.status || ""));
      });

      updateSyncLabel("Last synced");
    } catch (error) {
      console.error("Inventory live sync failed:", error);
    } finally {
      isSyncing = false;
    }
  }

  refreshInventoryFromServer();
  setInterval(refreshInventoryFromServer, AUTO_SYNC_MS);

  document.addEventListener("visibilitychange", function () {
    if (!document.hidden) {
      refreshInventoryFromServer();
    }
  });
});
