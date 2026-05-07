document.addEventListener("DOMContentLoaded", function () {
    const d = window.dashboardData || {};
    const hasApexCharts = typeof ApexCharts !== "undefined";

    if (!hasApexCharts) {
        console.warn("ApexCharts is not loaded. Dashboard charts are disabled.");
    }

    // Fuel Stock Chart
    const fuelChartEl = document.querySelector("#fuel-stock-chart");
    if (fuelChartEl && hasApexCharts) {
        const fuelChartOptions = {
            series: [
                {
                    name: "Remaining Liters",
                    data: d.fuelLiters || []
                },
                {
                    name: "Capacity %",
                    data: d.fuelPercentages || []
                }
            ],
            chart: {
                type: "bar",
                height: 350,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: "45%"
                }
            },
            dataLabels: {
                enabled: true
            },
            xaxis: {
                categories: d.fuelNames || []
            },
            yaxis: [
                {
                    title: {
                        text: "Remaining Liters"
                    }
                },
                {
                    opposite: true,
                    max: 100,
                    title: {
                        text: "Capacity %"
                    }
                }
            ],
            colors: ["#004289", "#ed1c24"],
            grid: {
                strokeDashArray: 4
            }
        };

        const fuelChart = new ApexCharts(fuelChartEl, fuelChartOptions);
        fuelChart.render();
    }

    // Sales Trend Graph
    const salesChartEl = document.querySelector("#sales-trend-graph");
    if (salesChartEl && hasApexCharts) {
        const salesChartOptions = {
            series: [
                {
                    name: "Sales",
                    data: d.salesValues || []
                }
            ],
            chart: {
                type: "line",
                height: 350,
                toolbar: { show: false }
            },
            stroke: {
                curve: "smooth",
                width: 4
            },
            markers: {
                size: 6
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return "₱" + Number(val).toFixed(0);
                }
            },
            xaxis: {
                categories: d.salesDates || []
            },
            yaxis: {
                title: {
                    text: "Sales Amount"
                }
            },
            annotations: {
                yaxis: [
                    {
                        y: d.minSales || 0,
                        borderColor: "#f59e0b",
                        label: {
                            borderColor: "#f59e0b",
                            style: {
                                color: "#fff",
                                background: "#f59e0b"
                            },
                            text: d.minSalesLabel || "Min Sales"
                        }
                    },
                    {
                        y: d.maxSales || 0,
                        borderColor: "#16a34a",
                        label: {
                            borderColor: "#16a34a",
                            style: {
                                color: "#fff",
                                background: "#16a34a"
                            },
                            text: d.maxSalesLabel || "Max Sales"
                        }
                    }
                ]
            },
            colors: ["#ed1c24"],
            grid: {
                strokeDashArray: 4
            }
        };

        const salesChart = new ApexCharts(salesChartEl, salesChartOptions);
        salesChart.render();
    }

    async function refreshDashboardStats() {
        try {
            const response = await fetch('/public/admin/ajax/dashboard_stats.php', {
                cache: 'no-store'
            });

            if (!response.ok) return;

            const data = await response.json();

            const salesTodayEl = document.getElementById('salesTodayValue');
            const litersTodayEl = document.getElementById('litersTodayValue');
            const totalFuelsEl = document.getElementById('totalFuelsValue');
            const lowStockEl = document.getElementById('lowStockValue');
            const stockAlertsBody = document.getElementById('stockAlertsBody');

            if (salesTodayEl) {
                salesTodayEl.innerHTML = '₱ ' + Number(data.salesToday || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            if (litersTodayEl) {
                litersTodayEl.innerHTML = Number(data.litersToday || 0).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) + ' <span class="text-sm font-light">L</span>';
            }

            if (totalFuelsEl) {
                totalFuelsEl.textContent = data.totalFuels || 0;
            }

            if (lowStockEl) {
                lowStockEl.textContent = (data.lowStock || []).length;
            }

            if (stockAlertsBody) {
                const lowStock = data.lowStock || [];

                if (lowStock.length > 0) {
                    stockAlertsBody.innerHTML = lowStock.map(stock => {
                        const liters = Number(stock.liters || 0);
                        const capacity = Number(stock.capacity || 0);
                        const percentage = capacity > 0 ? (liters / capacity) * 100 : 0;

                        return `
                            <tr class="hover:bg-gray-50/80 transition-colors duration-200">
                                <td class="px-6 py-4 font-bold text-gray-700">${escapeHtml(stock.fuel_name)}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold ${percentage <= 10 ? 'text-red-600' : 'text-gray-700'}">
                                            ${liters.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} L
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            ${percentage.toFixed(1)}% Capacity
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    ${
                                        percentage <= 10
                                        ? `<span class="inline-flex items-center px-4 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-black ring-2 ring-red-500/20 animate-pulse">
                                                <span class="w-2 h-2 rounded-full bg-red-600 mr-2"></span> CRITICAL
                                           </span>`
                                        : `<span class="inline-flex items-center px-4 py-1.5 rounded-full bg-amber-100 text-amber-700 text-xs font-black">
                                                WARNING
                                           </span>`
                                    }
                                </td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    stockAlertsBody.innerHTML = `
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic text-sm">
                                All stock levels are optimal.
                            </td>
                        </tr>
                    `;
                }
            }

        } catch (error) {
            console.error('Dashboard refresh failed:', error);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text ?? '';
        return div.innerHTML;
    }

    setInterval(refreshDashboardStats, 5000);
});
