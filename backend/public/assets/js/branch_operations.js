document.addEventListener("DOMContentLoaded", function () {
  const branchCountEl = document.getElementById("opsBranchCountValue");
  const salesTodayEl = document.getElementById("opsSalesTodayValue");
  const transactionsTodayEl = document.getElementById("opsTransactionsTodayValue");
  const litersTodayEl = document.getElementById("opsLitersTodayValue");
  const deliveriesTodayEl = document.getElementById("opsDeliveriesTodayValue");
  const lowStockCountEl = document.getElementById("opsLowStockCountValue");
  const operationsBody = document.getElementById("branchOperationsBody");
  const lastUpdatedEl = document.getElementById("opsLastUpdated");

  if (
    !branchCountEl ||
    !salesTodayEl ||
    !transactionsTodayEl ||
    !litersTodayEl ||
    !deliveriesTodayEl ||
    !lowStockCountEl ||
    !operationsBody
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

  function formatDateTimeParts(value) {
    if (!value) {
      return { date: "-", time: "" };
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
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
      return '<span class="text-sm text-gray-400">-</span>';
    }

    return `
      <p class="font-semibold text-gray-700 whitespace-nowrap">${escapeHtml(parts.date)}</p>
      <p class="text-xs text-gray-500 whitespace-nowrap">${escapeHtml(parts.time)}</p>
    `;
  }

  function renderUserCell(adminCount, staffCount) {
    return `
      <div class="flex flex-col gap-1">
        <span class="inline-flex w-fit items-center gap-1 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-bold">
          <i class="fa-solid fa-user-shield"></i> Admin: ${formatNumber(adminCount, 0)}
        </span>
        <span class="inline-flex w-fit items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 text-xs font-bold">
          <i class="fa-solid fa-user"></i> Staff: ${formatNumber(staffCount, 0)}
        </span>
      </div>
    `;
  }

  function formatLastUpdatedTime(value) {
    return value.toLocaleTimeString("en-US", {
      hour: "2-digit",
      minute: "2-digit"
    });
  }

  function getHealth(row) {
    const lowStock = Number(row.low_stock_count || 0);
    const transactions = Number(row.transactions_today || 0);
    const deliveries = Number(row.deliveries_today || 0);

    if (lowStock >= 2) {
      return {
        label: "Critical",
        className: "bg-red-100 text-red-700"
      };
    }

    if (lowStock > 0) {
      return {
        label: "Needs Attention",
        className: "bg-amber-100 text-amber-700"
      };
    }

    if (lowStock === 0 && transactions === 0 && deliveries === 0) {
      return {
        label: "No Activity",
        className: "bg-gray-100 text-gray-700"
      };
    }

    return {
      label: "Normal",
      className: "bg-emerald-100 text-emerald-700"
    };
  }

  function renderOperationsRows(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      operationsBody.innerHTML = `
        <tr>
          <td colspan="11" class="px-6 py-10 text-center text-gray-400 italic">No branch operation data found.</td>
        </tr>
      `;
      return;
    }

    operationsBody.innerHTML = rows.map((row) => {
      const lowStock = Number(row.low_stock_count || 0);
      const health = getHealth(row);

      const lowStockBadge = lowStock > 0
        ? `<span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold">${formatNumber(lowStock, 0)} alert${lowStock > 1 ? "s" : ""}</span>`
        : '<span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-extrabold">OK</span>';

      return `
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4">
            <p class="font-bold text-gray-900">${escapeHtml(row.branch_name || "-")}</p>
            <p class="text-xs text-gray-500">${escapeHtml(row.location || "-")}</p>
          </td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ ${formatNumber(row.sales_today, 2)}</td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.transactions_today, 0)}</td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.liters_today, 2)} L</td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
            ${formatNumber(row.deliveries_today, 0)}
            <span class="text-xs text-gray-500">(${formatNumber(row.delivered_liters_today, 2)} L)</span>
          </td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${formatNumber(row.total_stock_liters, 2)} L</td>
          <td class="px-6 py-4 whitespace-nowrap">${lowStockBadge}</td>
          <td class="px-6 py-4">${renderUserCell(row.admin_count, row.staff_count)}</td>
          <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">${renderDateCell(row.last_sale_at)}</td>
          <td class="px-6 py-4 text-gray-600 text-sm whitespace-nowrap">${renderDateCell(row.last_delivery_at)}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold ${health.className}">${health.label}</span>
          </td>
        </tr>
      `;
    }).join("");
  }

  function applyOperationsData(data) {
    const totals = data?.totals || {};
    const rows = data?.rows || [];

    branchCountEl.textContent = formatNumber(totals.branch_count, 0);
    salesTodayEl.textContent = `₱ ${formatNumber(totals.sales_today, 2)}`;
    transactionsTodayEl.textContent = formatNumber(totals.transactions_today, 0);
    litersTodayEl.textContent = `${formatNumber(totals.liters_today, 2)} L`;
    deliveriesTodayEl.textContent = formatNumber(totals.deliveries_today, 0);
    lowStockCountEl.textContent = formatNumber(totals.low_stock_count, 0);
    renderOperationsRows(rows);

    if (lastUpdatedEl) {
      const now = new Date();
      lastUpdatedEl.textContent = `Last updated: ${formatLastUpdatedTime(now)}`;
    }
  }

  async function refreshBranchOperations() {
    if (isRefreshing) {
      return;
    }

    isRefreshing = true;
    try {
      const response = await fetch("/public/superadmin/ajax/branch_operations_stats.php", {
        cache: "no-store"
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      if (!payload.success || !payload.data) {
        return;
      }

      applyOperationsData(payload.data);
    } catch (error) {
      console.error("Branch operations refresh failed:", error);
    } finally {
      isRefreshing = false;
    }
  }

  if (window.branchOperationsData) {
    applyOperationsData(window.branchOperationsData);
  }

  setInterval(refreshBranchOperations, 5000);
});
