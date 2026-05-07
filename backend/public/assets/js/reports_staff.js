const data = window.reportsData || {};

const dailyLabels = data.dailyLabels || [];
const dailySales = data.dailySales || [];
const monthlyLabels = data.monthlyLabels || [];
const monthlySales = data.monthlySales || [];
const hasChartJs = typeof Chart !== "undefined";

if (!hasChartJs) {
    console.warn("Chart.js is not loaded. Report charts are disabled.");
}

// Doughnut
const dailyChartEl = document.getElementById('dailyFuelChart');
if (dailyChartEl && hasChartJs) {
    new Chart(dailyChartEl, {
        type: 'doughnut',
        data: {
            labels: dailyLabels,
            datasets: [{
                data: dailySales,
                backgroundColor: ['#004289', '#ed1c24', '#ffc107', '#198754']
            }]
        },
        options: {
            cutout: '70%',
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Line
const monthlyChartEl = document.getElementById('monthlyTrendChart');
if (monthlyChartEl && hasChartJs) {
    new Chart(monthlyChartEl, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                data: monthlySales,
                borderColor: '#004289',
                backgroundColor: 'rgba(0,66,137,0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
}
