document.addEventListener("DOMContentLoaded", function () {
  const salesTodayEl = document.getElementById("salesTodayValue");
  const litersTodayEl = document.getElementById("litersTodayValue");
  const fuelCountEl = document.getElementById("fuelCountValue");
  const totalBranchesEl = document.getElementById("totalBranchesValue");
  const adminCountEl = document.getElementById("adminCountValue");
  const staffCountEl = document.getElementById("staffCountValue");
  const branchSummaryBody = document.getElementById("branchSummaryBody");
  const lowStockBody = document.getElementById("lowStockBody");

  const branchPerformanceChartEl = document.getElementById("branchPerformanceChart");
  const accountMixChartEl = document.getElementById("accountMixChart");
  const salesTrendChartEl = document.getElementById("salesTrendChart");

  if (!salesTodayEl || !branchSummaryBody || !lowStockBody) {
    return;
  }

  let isRefreshing = false;
  let branchPerformanceChart = null;
  let accountMixChart = null;
  let salesTrendChart = null;

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

  function buildChartData(data) {
    const summaryRows = Array.isArray(data.branchSummary) ? data.branchSummary : [];
    const trendRows = Array.isArray(data.salesTrend) ? data.salesTrend : [];

    const branchCategories = summaryRows.map((row) => row.branch_name ?? "Unknown");
    const branchSales = summaryRows.map((row) => Number(row.sales_today || 0));
    const branchLiters = summaryRows.map((row) => Number(row.liters_today || 0));

    const trendCategories = trendRows.map((row) => row.label ?? row.sale_day ?? "");
    const trendSales = trendRows.map((row) => Number(row.total_sales || 0));

    return {
      branchCategories,
      branchSales,
      branchLiters,
      accountSeries: [
        Number(data.adminCount || 0),
        Number(data.staffCount || 0)
      ],
      trendCategories,
      trendSales
    };
  }

  function renderBranchSummary(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      branchSummaryBody.innerHTML = `
        <tr>
          <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">No branch data available.</td>
        </tr>
      `;
      return;
    }

    branchSummaryBody.innerHTML = rows.map((branch) => `
      <tr class="hover:bg-gray-50 transition">
        <td class="px-6 py-4 font-bold text-gray-900">${escapeHtml(branch.branch_name)}</td>
        <td class="px-6 py-4 font-semibold text-gray-700">₱ ${formatNumber(branch.sales_today, 2)}</td>
        <td class="px-6 py-4 font-semibold text-gray-700">${formatNumber(branch.liters_today, 2)} L</td>
        <td class="px-6 py-4 font-semibold text-gray-700">${formatNumber(branch.total_fuels, 0)}</td>
        <td class="px-6 py-4 text-gray-700">${escapeHtml(branch.admin_usernames || "-")}</td>
      </tr>
    `).join("");
  }

  function renderLowStock(rows) {
    if (!Array.isArray(rows) || rows.length === 0) {
      lowStockBody.innerHTML = `
        <tr>
          <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No low stock alerts right now.</td>
        </tr>
      `;
      return;
    }

    lowStockBody.innerHTML = rows.map((stock) => {
      const liters = Number(stock.liters || 0);
      const capacity = Number(stock.capacity || 0);
      const percentage = capacity > 0 ? (liters / capacity) * 100 : 0;
      const status = percentage <= 10
        ? '<span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-extrabold">CRITICAL</span>'
        : '<span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-extrabold">WARNING</span>';

      return `
        <tr class="hover:bg-gray-50 transition">
          <td class="px-6 py-4 font-semibold text-gray-700">${escapeHtml(stock.branch_name)}</td>
          <td class="px-6 py-4 font-semibold text-gray-700">${escapeHtml(stock.fuel_name)}</td>
          <td class="px-6 py-4 font-semibold text-gray-700">${formatNumber(liters, 2)} L (${formatNumber(percentage, 1)}%)</td>
          <td class="px-6 py-4">${status}</td>
        </tr>
      `;
    }).join("");
  }

  function initializeCharts(data) {
    if (typeof ApexCharts === "undefined") {
      return;
    }

    const chartData = buildChartData(data);

    if (branchPerformanceChartEl) {
      branchPerformanceChart = new ApexCharts(branchPerformanceChartEl, {
        series: [
          { name: "Sales Today", type: "column", data: chartData.branchSales },
          { name: "Liters Today", type: "line", data: chartData.branchLiters }
        ],
        chart: {
          type: "line",
          height: 320,
          toolbar: { show: false }
        },
        stroke: {
          width: [0, 3],
          curve: "smooth"
        },
        plotOptions: {
          bar: {
            borderRadius: 6,
            columnWidth: "45%"
          }
        },
        dataLabels: {
          enabled: false
        },
        xaxis: {
          categories: chartData.branchCategories
        },
        yaxis: [
          {
            title: { text: "Sales (PHP)" },
            labels: {
              formatter: (value) => `₱${formatNumber(value, 0)}`
            }
          },
          {
            opposite: true,
            title: { text: "Liters" },
            labels: {
              formatter: (value) => `${formatNumber(value, 0)} L`
            }
          }
        ],
        colors: ["#004289", "#ed1c24"],
        grid: {
          strokeDashArray: 4
        },
        tooltip: {
          shared: true,
          intersect: false,
          y: [
            { formatter: (value) => `₱ ${formatNumber(value, 2)}` },
            { formatter: (value) => `${formatNumber(value, 2)} L` }
          ]
        }
      });
      branchPerformanceChart.render();
    }

    if (accountMixChartEl) {
      accountMixChart = new ApexCharts(accountMixChartEl, {
        series: chartData.accountSeries,
        labels: ["Admin", "Staff"],
        chart: {
          type: "donut",
          height: 320
        },
        colors: ["#f59e0b", "#2563eb"],
        legend: {
          position: "bottom"
        },
        dataLabels: {
          enabled: true
        },
        plotOptions: {
          pie: {
            donut: {
              size: "60%"
            }
          }
        }
      });
      accountMixChart.render();
    }

    if (salesTrendChartEl) {
      salesTrendChart = new ApexCharts(salesTrendChartEl, {
        series: [
          { name: "Regional Sales", data: chartData.trendSales }
        ],
        chart: {
          type: "area",
          height: 320,
          toolbar: { show: false }
        },
        stroke: {
          curve: "smooth",
          width: 3
        },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.05,
            stops: [0, 90, 100]
          }
        },
        xaxis: {
          categories: chartData.trendCategories
        },
        yaxis: {
          labels: {
            formatter: (value) => `₱${formatNumber(value, 0)}`
          }
        },
        colors: ["#0ea5e9"],
        dataLabels: {
          enabled: false
        },
        grid: {
          strokeDashArray: 4
        },
        tooltip: {
          y: {
            formatter: (value) => `₱ ${formatNumber(value, 2)}`
          }
        }
      });
      salesTrendChart.render();
    }
  }

  function updateCharts(data) {
    const chartData = buildChartData(data);

    if (branchPerformanceChart) {
      branchPerformanceChart.updateOptions({
        xaxis: { categories: chartData.branchCategories }
      }, false, true);
      branchPerformanceChart.updateSeries([
        { name: "Sales Today", type: "column", data: chartData.branchSales },
        { name: "Liters Today", type: "line", data: chartData.branchLiters }
      ], true);
    }

    if (accountMixChart) {
      accountMixChart.updateSeries(chartData.accountSeries, true);
    }

    if (salesTrendChart) {
      salesTrendChart.updateOptions({
        xaxis: { categories: chartData.trendCategories }
      }, false, true);
      salesTrendChart.updateSeries([
        { name: "Regional Sales", data: chartData.trendSales }
      ], true);
    }
  }

  function applyDashboardData(data) {
    salesTodayEl.textContent = `₱ ${formatNumber(data.salesToday, 2)}`;
    litersTodayEl.textContent = `${formatNumber(data.litersToday, 2)} L`;
    fuelCountEl.textContent = formatNumber(data.fuelCount, 0);
    totalBranchesEl.textContent = formatNumber(data.totalBranches, 0);
    adminCountEl.textContent = formatNumber(data.adminCount, 0);
    staffCountEl.textContent = formatNumber(data.staffCount, 0);

    renderBranchSummary(data.branchSummary || []);
    renderLowStock(data.lowStock || []);

    if (!branchPerformanceChart && !accountMixChart && !salesTrendChart) {
      initializeCharts(data);
    } else {
      updateCharts(data);
    }
  }

  async function refreshSuperAdminDashboard() {
    if (isRefreshing) {
      return;
    }

    isRefreshing = true;
    try {
      const response = await fetch("/petron_system/public/superadmin/ajax/dashboard_stats.php", {
        cache: "no-store"
      });

      if (!response.ok) {
        return;
      }

      const payload = await response.json();
      if (!payload.success || !payload.data) {
        return;
      }

      applyDashboardData(payload.data);
    } catch (error) {
      console.error("Super admin dashboard refresh failed:", error);
    } finally {
      isRefreshing = false;
    }
  }

  if (window.superAdminDashboardData) {
    applyDashboardData(window.superAdminDashboardData);
  }

  setInterval(refreshSuperAdminDashboard, 5000);
});

