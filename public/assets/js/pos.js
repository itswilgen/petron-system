const posForm = document.getElementById("posForm");
const fuel = document.getElementById("fuel");
const liters = document.getElementById("liters");
const total = document.getElementById("total");
const sumName = document.getElementById("sum-name");
const sumLiters = document.getElementById("sum-liters");
const fuelStockInfo = document.getElementById("fuel-stock-info");
const posMessage = document.getElementById("posMessage");

function compute() {
    const selected = fuel?.options[fuel.selectedIndex];
    const price = Number(selected?.getAttribute("data-price") || 0);
    const name = selected?.getAttribute("data-name") || "-";
    const stock = Number(selected?.getAttribute("data-liters") || 0);
    const l = Number(liters?.value || 0);

    if (total) {
        total.innerText = (price * l).toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    if (sumName) sumName.innerText = name;
    if (sumLiters) sumLiters.innerText = l > 0 ? l.toFixed(2) + " L" : "0.00 L";

    if (fuelStockInfo) {
        fuelStockInfo.innerText = "Current Stock: " + stock.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }) + " L";
    }
}

function setLiters(val) {
    if (liters) {
        liters.value = val;
        compute();
    }
}

window.setLiters = setLiters;

function showMessage(message, isSuccess = true) {
    if (!posMessage) return;

    posMessage.classList.remove(
        "hidden",
        "bg-red-100", "border-red-300", "text-red-800",
        "bg-emerald-100", "border-emerald-300", "text-emerald-800"
    );

    posMessage.classList.add(
        isSuccess ? "bg-emerald-100" : "bg-red-100",
        isSuccess ? "border-emerald-300" : "border-red-300",
        isSuccess ? "text-emerald-800" : "text-red-800",
        "border"
    );

    posMessage.textContent = message;
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text ?? '';
    return div.innerHTML;
}

async function refreshPosFuels(selectedFuelId = null) {
    try {
        const response = await fetch('/petron_system/public/staff/ajax/pos_fuels.php', {
            cache: 'no-store'
        });

        if (!response.ok) return;

        const data = await response.json();
        if (!data.success || !fuel) return;

        const currentSelected = selectedFuelId || fuel.value;

        fuel.innerHTML = `<option value="">Select Fuel Type</option>`;

        data.fuels.forEach(row => {
            const option = document.createElement("option");
            option.value = row.id;
            option.setAttribute("data-price", row.price);
            option.setAttribute("data-name", row.fuel_name);
            option.setAttribute("data-liters", row.liters);
            option.textContent = `${row.fuel_name} (₱${Number(row.price).toFixed(2)}/L)`;
            fuel.appendChild(option);
        });

        if (currentSelected) {
            fuel.value = String(currentSelected);
        }

        compute();
    } catch (error) {
        console.error("POS fuel refresh failed:", error);
    }
}

async function refreshSalesHistory() {
    try {
        const response = await fetch('/petron_system/public/staff/ajax/pos_sales_history.php', {
            cache: 'no-store'
        });

        if (!response.ok) return;

        const data = await response.json();
        if (!data.success) return;

        const salesHistoryBody = document.getElementById("salesHistoryBody");
        const totalTransactionsText = document.getElementById("totalTransactionsText");

        if (totalTransactionsText) {
            totalTransactionsText.textContent = `Total Transactions: ${data.total}`;
        }

        if (!salesHistoryBody) return;

        const rows = data.rows || [];

        if (rows.length === 0) {
            salesHistoryBody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">
                        No sales records found.
                    </td>
                </tr>
            `;
            return;
        }

        salesHistoryBody.innerHTML = rows.map(sale => `
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 font-extrabold text-gray-700">#${sale.id}</td>
                <td class="px-6 py-4 font-bold text-petron-blue">${escapeHtml(sale.fuel_name)}</td>
                <td class="px-6 py-4 font-bold">
                    ${Number(sale.liters).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })} L
                </td>
                <td class="px-6 py-4 font-bold text-green-700">
                    ₱ ${Number(sale.total_price).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}
                </td>
                <td class="px-6 py-4 text-gray-600">${escapeHtml(sale.sale_date)}</td>
            </tr>
        `).join('');
    } catch (error) {
        console.error("Sales history refresh failed:", error);
    }
}

if (fuel) fuel.addEventListener("change", compute);
if (liters) liters.addEventListener("input", compute);

if (posForm) {
    posForm.addEventListener("submit", async function(e) {
        e.preventDefault();

        const formData = new FormData(posForm);
        formData.append("pay", "1");

        try {
            const response = await fetch('/petron_system/public/staff/ajax/process_sale.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showMessage(data.message, true);

                const selectedFuelId = fuel?.value || null;

                posForm.reset();
                if (total) total.innerText = "0.00";
                if (sumName) sumName.innerText = "-";
                if (sumLiters) sumLiters.innerText = "0.00 L";

                await refreshPosFuels(selectedFuelId);
                await refreshSalesHistory();
            } else {
                showMessage(data.message || "Sale failed.", false);
            }
        } catch (error) {
            console.error(error);
            showMessage("Something went wrong while processing the sale.", false);
        }
    });
}

compute();