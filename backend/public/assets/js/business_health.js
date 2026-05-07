document.addEventListener("DOMContentLoaded", function () {
  const revenue7dEl = document.getElementById("bizRevenue7dValue");
  const trendAmountEl = document.getElementById("bizTrendAmountValue");
  const trendPercentEl = document.getElementById("bizTrendPercentValue");
  const healthyBranchesEl = document.getElementById("bizHealthyBranchesValue");
  const avgScoreEl = document.getElementById("bizAvgScoreValue");
  const riskCountEl = document.getElementById("bizRiskCountValue");
  const tableBody = document.getElementById("businessHealthBody");
  const lastUpdatedEl = document.getElementById("bizLastUpdated");

  if (
    !revenue7dEl ||
    !trendAmountEl ||
    !trendPercentEl ||
    !healthyBranchesEl ||
    !avgScoreEl ||
    !riskCountEl ||
    !tableBody
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

  function getHealthBadge(health) {
    if (health === "Good") {
      return "bg-emerald-100 text-emerald-700";
    }
    if (health === "Bad") {
      return "bg-red-100 text-red-700";
    }
    return "bg-amber-100 text-amber-700";
  }

  function renderRows(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="12" class="px-6 py-10 text-center text-gray-400 italic">No business health data available.</td>
        </tr>
      `;
      return;
    }

    tableBody.innerHTML = rows.map((row) => {
      const trendAmount = Number(row.trend_amount || 0);
      const trendPercent = Number(row.trend_percent || 0);
      const positiveTrend = trendAmount >= 0;
      const health = row.health || "Warning";
      const healthClass = getHealthBadge(health);
      const lowStock = Number(row.low_stock_count || 0);
      const coverageDays = row.stock_coverage_days == null
        ? "-"
        : `${formatNumber(row.stock_coverage_days, 1)} days`;

      const lowStockBadge = lowStock > 0
        ? `<span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold">${formatNumber(lowStock, 0)} alert${lowStock > 1 ? "s" : ""}</span>`
        : '<span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-extrabold">OK</span>';

      return `
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4">
            <p class="font-bold text-gray-900">${escapeHtml(row.branch_name || "-")}</p>
            <p class="text-xs text-gray-500">${escapeHtml(row.location || "-")}</p>
          </td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ ${formatNumber(row.revenue_7d, 2)}</td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ ${formatNumber(row.revenue_prev_7d, 2)}</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <p class="font-bold ${positiveTrend ? "text-emerald-600" : "text-red-600"}">
              ${positiveTrend ? "+" : "-"}₱ ${formatNumber(Math.abs(trendAmount), 2)}
            </p>
            <p class="text-xs font-semibold ${positiveTrend ? "text-emerald-600" : "text-red-600"}">
              ${positiveTrend ? "+" : ""}${formatNumber(trendPercent, 1)}%
            </p>
          </td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
            ${formatNumber(row.transactions_7d, 0)}
            <span class="text-xs text-gray-500">(Today: ${formatNumber(row.transactions_today, 0)})</span>
          </td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">₱ ${formatNumber(row.avg_ticket_7d, 2)}</td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">${coverageDays}</td>
          <td class="px-6 py-4 whitespace-nowrap">${lowStockBadge}</td>
          <td class="px-6 py-4 font-semibold text-gray-700 whitespace-nowrap">
            ${formatNumber(row.deliveries_7d, 0)}
            <span class="text-xs text-gray-500">(${formatNumber(row.delivered_liters_7d, 2)} L)</span>
          </td>
          <td class="px-6 py-4 font-bold text-gray-900 whitespace-nowrap">${formatNumber(row.health_score, 0)}/100</td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-extrabold ${healthClass}">${escapeHtml(health)}</span>
          </td>
          <td class="px-6 py-4 text-sm text-gray-600 min-w-90">${escapeHtml(row.note || "-")}</td>
        </tr>
      `;
    }).join("");
  }

  function applySummary(summary) {
    const revenue7d = Number(summary.revenue_7d || 0);
    const trendAmount = Number(summary.trend_amount || 0);
    const trendPercent = Number(summary.trend_percent || 0);
    const trendPositive = trendAmount >= 0;
    const goodCount = Number(summary.good_count || 0);
    const branchCount = Number(summary.branch_count || 0);
    const warningCount = Number(summary.warning_count || 0);
    const badCount = Number(summary.bad_count || 0);
    const avgHealthScore = Number(summary.avg_health_score || 0);

    revenue7dEl.textContent = `₱ ${formatNumber(revenue7d, 2)}`;

    trendAmountEl.textContent = `${trendPositive ? "+" : "-"}₱ ${formatNumber(Math.abs(trendAmount), 2)}`;
    trendAmountEl.classList.remove("text-emerald-600", "text-red-600");
    trendAmountEl.classList.add(trendPositive ? "text-emerald-600" : "text-red-600");

    trendPercentEl.textContent = `${trendPositive ? "+" : ""}${formatNumber(trendPercent, 1)}%`;
    trendPercentEl.classList.remove("text-emerald-600", "text-red-600");
    trendPercentEl.classList.add(trendPositive ? "text-emerald-600" : "text-red-600");

    healthyBranchesEl.textContent = `${formatNumber(goodCount, 0)}/${formatNumber(branchCount, 0)}`;
    avgScoreEl.textContent = `${formatNumber(avgHealthScore, 0)}/100`;
    riskCountEl.textContent = `At Risk: ${formatNumber(warningCount + badCount, 0)} branch(es)`;
  }

  function applyData(data) {
    const summary = data?.summary || {};
    const rows = data?.rows || [];

    applySummary(summary);
    renderRows(rows);

    if (lastUpdatedEl) {
      const now = new Date();
      lastUpdatedEl.textContent = `Last updated: ${now.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit"
      })}`;
    }
  }

  async function refreshBusinessHealth() {
    if (isRefreshing) {
      return;
    }

    isRefreshing = true;
    try {
      const response = await fetch("/public/superadmin/ajax/business_health_stats.php", {
        cache: "no-store"
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      if (!payload.success || !payload.data) {
        return;
      }

      applyData(payload.data);
    } catch (error) {
      console.error("Business health refresh failed:", error);
    } finally {
      isRefreshing = false;
    }
  }

  if (window.businessHealthData) {
    applyData(window.businessHealthData);
  }

  setInterval(refreshBusinessHealth, 5000);
});
