document.addEventListener("DOMContentLoaded", function () {
  const totalSalesEl = document.getElementById("globalSalesTotalValue");
  const transactionsEl = document.getElementById("globalSalesTransactionsValue");
  const litersEl = document.getElementById("globalSalesLitersValue");
  const activeBranchesEl = document.getElementById("globalSalesActiveBranchesValue");
  const latestSaleTextEl = document.getElementById("globalSalesLatestSaleText");
  const averageTicketTextEl = document.getElementById("globalSalesAverageTicketText");
  const recentSalesBody = document.getElementById("globalRecentSalesBody");
  const branchBody = document.getElementById("globalSalesBranchBody");
  const fuelBody = document.getElementById("globalSalesFuelBody");
  const lastUpdatedEl = document.getElementById("globalRecentSalesLastUpdated");
  const dateInput = document.getElementById("globalSalesDateFilter");

  if (
    !totalSalesEl ||
    !transactionsEl ||
    !litersEl ||
    !activeBranchesEl ||
    !recentSalesBody ||
    !branchBody ||
    !fuelBody
  ) {
    return;
  }

  let isRefreshing = false;

  function escapeHtml(value) {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
  }

  function formatNumber(value, decimals = 0) {
    const number = Number(value || 0);
    return number.toLocaleString("en-US", {
      minimumFractionDigits: decimals,
      maximumFractionDigits: decimals
    });
  }

  function formatReference(id) {
    return `TXN-${String(Number(id || 0)).padStart(6, "0")}`;
  }

  function parseDate(value) {
    if (!value) {
      return null;
    }

    const normalizedValue = String(value).replace(" ", "T");
    const date = new Date(normalizedValue);
    if (Number.isNaN(date.getTime())) {
      return null;
    }

    return date;
  }

  function formatDateTimeParts(value) {
    const date = parseDate(value);
    if (!date) {
      return { date: "-", time: "" };
    }

    return {
      date: date.toLocaleDateString("en-US", {
        year: "numeric",
        month: "short",
        day: "2-digit"
      }),
      time: date.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit"
      })
    };
  }

  function renderDateCell(value) {
    const parts = formatDateTimeParts(value);
    if (parts.date === "-") {
      return '<span class="text-gray-400">-</span>';
    }

    return `
      <p class="font-semibold text-gray-700">${escapeHtml(parts.date)}</p>
      <p class="text-xs text-gray-500">${escapeHtml(parts.time)}</p>
    `;
  }

  function formatLastUpdatedTime(value) {
    return value.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit"
    });
  }

  function readInitialData() {
    const dataScript = document.getElementById("globalRecentSalesInitialData");
    if (!dataScript) {
      return null;
    }

    try {
      return JSON.parse(dataScript.textContent || "{}");
    } catch (error) {
      console.error("Global recent sales initial data is invalid:", error);
      return null;
    }
  }

  function renderRecentRows(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      recentSalesBody.innerHTML = `
        <tr>
          <td colspan="7" class="px-6 py-10 text-center text-gray-400 italic">No sales records found for this date.</td>
        </tr>
      `;
      return;
    }

    recentSalesBody.innerHTML = rows.map((sale) => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-6 py-4 font-bold text-petron-blue whitespace-nowrap">${escapeHtml(formatReference(sale.id))}</td>
        <td class="px-6 py-4">
          <p class="font-bold text-gray-900">${escapeHtml(sale.branch_name || "-")}</p>
          <p class="text-xs text-gray-500">${escapeHtml(sale.location || "-")}</p>
        </td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${escapeHtml(sale.fuel_name || "-")}</td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(sale.liters, 2)} L</td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ ${formatNumber(sale.price, 2)}</td>
        <td class="px-6 py-4 font-bold text-emerald-700 whitespace-nowrap">₱ ${formatNumber(sale.total_price, 2)}</td>
        <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">${renderDateCell(sale.sale_date)}</td>
      </tr>
    `).join("");
  }

  function renderBranchRows(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      branchBody.innerHTML = `
        <tr>
          <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No branch data found.</td>
        </tr>
      `;
      return;
    }

    branchBody.innerHTML = rows.map((row) => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-6 py-4">
          <p class="font-bold text-gray-900">${escapeHtml(row.branch_name || "-")}</p>
          <p class="text-xs text-gray-500">${escapeHtml(row.location || "-")}</p>
        </td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.transaction_count, 0)}</td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.total_liters, 2)} L</td>
        <td class="px-6 py-4 font-bold text-emerald-700 whitespace-nowrap">₱ ${formatNumber(row.total_sales, 2)}</td>
        <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">${renderDateCell(row.latest_sale_at)}</td>
      </tr>
    `).join("");
  }

  function renderFuelRows(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      fuelBody.innerHTML = `
        <tr>
          <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No fuel sales found for this date.</td>
        </tr>
      `;
      return;
    }

    fuelBody.innerHTML = rows.map((row) => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-6 py-4 font-bold text-gray-900">${escapeHtml(row.fuel_name || "-")}</td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.transaction_count, 0)}</td>
        <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.total_liters, 2)} L</td>
        <td class="px-6 py-4 font-bold text-emerald-700 whitespace-nowrap">₱ ${formatNumber(row.total_sales, 2)}</td>
      </tr>
    `).join("");
  }

  function applyGlobalRecentSalesData(data) {
    const summary = data?.summary || {};
    const latestParts = formatDateTimeParts(summary.latest_sale_at);
    const latestSaleText = latestParts.date === "-"
      ? "No sales recorded for this date"
      : `Latest sale: ${latestParts.date} ${latestParts.time}`;

    totalSalesEl.textContent = `₱ ${formatNumber(summary.total_sales, 2)}`;
    transactionsEl.textContent = formatNumber(summary.transaction_count, 0);
    litersEl.textContent = `${formatNumber(summary.total_liters, 2)} L`;
    activeBranchesEl.textContent = `${formatNumber(summary.active_branch_count, 0)}/${formatNumber(summary.branch_count, 0)}`;

    if (latestSaleTextEl) {
      latestSaleTextEl.textContent = latestSaleText;
    }

    if (averageTicketTextEl) {
      averageTicketTextEl.textContent = `Average Ticket: ₱ ${formatNumber(summary.average_ticket, 2)}`;
    }

    renderRecentRows(data?.recentSales || []);
    renderBranchRows(data?.branchRows || []);
    renderFuelRows(data?.fuelRows || []);

    if (lastUpdatedEl) {
      lastUpdatedEl.textContent = `Last updated: ${formatLastUpdatedTime(new Date())}`;
    }
  }

  async function refreshGlobalRecentSales() {
    if (isRefreshing) {
      return;
    }

    isRefreshing = true;
    try {
      const url = new URL("/public/superadmin/ajax/global_recent_sales_stats.php", window.location.origin);
      if (dateInput?.value) {
        url.searchParams.set("date", dateInput.value);
      }

      const response = await fetch(url.toString(), {
        cache: "no-store"
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      if (!payload.success || !payload.data) {
        return;
      }

      applyGlobalRecentSalesData(payload.data);
    } catch (error) {
      console.error("Global recent sales refresh failed:", error);
    } finally {
      isRefreshing = false;
    }
  }

  const initialData = readInitialData();

  if (initialData) {
    applyGlobalRecentSalesData(initialData);
  }

  if (initialData?.isToday) {
    setInterval(refreshGlobalRecentSales, 5000);
  }
});
