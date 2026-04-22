const posForm = document.getElementById("posForm");
const fuel = document.getElementById("fuel");
const amount = document.getElementById("amount");
const liters = document.getElementById("liters");
const total = document.getElementById("total");
const sumName = document.getElementById("sum-name");
const sumLiters = document.getElementById("sum-liters");
const sumAmount = document.getElementById("sum-amount");
const fuelStockInfo = document.getElementById("fuel-stock-info");
const posMessage = document.getElementById("posMessage");
const printReceiptBtn = document.getElementById("printReceiptBtn");
const POS_AUTO_REFRESH_MS = 5000;
let lastReceiptData = null;
let posFuelRefreshInFlight = false;
let salesHistoryRefreshInFlight = false;

function formatNumber(value) {
    return Number(value || 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function compute() {
    const selected = fuel?.options[fuel.selectedIndex];
    const price = Number(selected?.getAttribute("data-price") || 0);
    const name = selected?.getAttribute("data-name") || "-";
    const stock = Number(selected?.getAttribute("data-liters") || 0);
    const amountValue = Number(amount?.value || 0);
    const litersValue = price > 0 ? (amountValue / price) : 0;

    if (total) {
        total.innerText = formatNumber(amountValue);
    }

    if (sumName) sumName.innerText = name;
    if (sumLiters) sumLiters.innerText = litersValue > 0 ? litersValue.toFixed(2) + " L" : "0.00 L";
    if (sumAmount) sumAmount.innerText = "₱ " + formatNumber(amountValue);

    if (liters) {
        liters.value = litersValue > 0 ? litersValue.toFixed(4) : "";
    }

    if (fuelStockInfo) {
        fuelStockInfo.innerText = "Current Stock: " + formatNumber(stock) + " L";
    }
}

function setAmount(val) {
    if (amount) {
        amount.value = val;
        compute();
    }
}

window.setAmount = setAmount;

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

function setPrintReceiptButtonState(enabled) {
    if (!printReceiptBtn) return;

    printReceiptBtn.disabled = !enabled;
    printReceiptBtn.classList.toggle("cursor-not-allowed", !enabled);
    printReceiptBtn.classList.toggle("text-gray-400", !enabled);
    printReceiptBtn.classList.toggle("border-gray-200", !enabled);
    printReceiptBtn.classList.toggle("text-gray-700", enabled);
    printReceiptBtn.classList.toggle("border-gray-300", enabled);
    printReceiptBtn.classList.toggle("hover:bg-gray-50", enabled);
}

function buildReceiptHtml(receipt) {
    const saleId = Number(receipt?.sale_id || 0);
    const reference = receipt?.reference || (saleId > 0 ? `TXN-${String(saleId).padStart(6, "0")}` : "TXN-000000");
    const saleDate = receipt?.sale_date || new Date().toLocaleString();
    const branchName = receipt?.branch_name || "Petron Branch";
    const cashier = receipt?.cashier || "Staff";
    const fuelName = receipt?.fuel_name || "-";
    const litersValue = Number(receipt?.liters || 0);
    const priceValue = Number(receipt?.price || 0);
    const totalValue = Number(receipt?.total_price || 0);

    return `<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Receipt ${escapeHtml(reference)}</title>
<style>
    body{font-family:Arial,sans-serif;background:#fff;color:#111;margin:0;padding:16px}
    .receipt{max-width:320px;margin:0 auto}
    h1{font-size:16px;margin:0 0 4px;text-align:center}
    p{margin:0 0 2px;text-align:center;font-size:12px}
    .line{border-top:1px dashed #111;margin:10px 0}
    .row{display:flex;justify-content:space-between;font-size:12px;margin:4px 0}
    .strong{font-weight:700}
    .center{text-align:center}
    @media print{
        body{padding:0}
        .receipt{max-width:none}
    }
</style>
</head>
<body>
    <div class="receipt">
        <h1>PETRON COMMAND CENTER</h1>
        <p>${escapeHtml(branchName)}</p>
        <p>${escapeHtml(saleDate)}</p>
        <div class="line"></div>
        <div class="row"><span>Receipt No</span><span class="strong">${escapeHtml(reference)}</span></div>
        <div class="row"><span>Cashier</span><span>${escapeHtml(cashier)}</span></div>
        <div class="line"></div>
        <div class="row"><span>Fuel</span><span>${escapeHtml(fuelName)}</span></div>
        <div class="row"><span>Liters</span><span>${litersValue.toFixed(2)} L</span></div>
        <div class="row"><span>Price/L</span><span>PHP ${priceValue.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span></div>
        <div class="line"></div>
        <div class="row strong"><span>TOTAL</span><span>PHP ${totalValue.toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span></div>
        <div class="line"></div>
        <p class="center">Thank you for your purchase.</p>
    </div>
</body>
</html>`;
}

function printLastReceipt() {
    if (!lastReceiptData) {
        showMessage("No receipt available yet. Complete one sale first.", false);
        return;
    }

    const printWindow = window.open("", "_blank", "width=420,height=640");
    if (!printWindow) {
        showMessage("Popup blocked. Please allow popups to print receipts.", false);
        return;
    }

    printWindow.document.open();
    printWindow.document.write(buildReceiptHtml(lastReceiptData));
    printWindow.document.close();

    printWindow.onload = function() {
        printWindow.focus();
        printWindow.print();
        printWindow.onafterprint = function() {
            printWindow.close();
        };
    };
}

async function refreshPosFuels(selectedFuelId = null) {
    if (posFuelRefreshInFlight) return;
    posFuelRefreshInFlight = true;

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
    } finally {
        posFuelRefreshInFlight = false;
    }
}

async function refreshSalesHistory() {
    if (salesHistoryRefreshInFlight) return;
    salesHistoryRefreshInFlight = true;

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
                    <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">
                        No sales records found.
                    </td>
                </tr>
            `;
            return;
        }

        salesHistoryBody.innerHTML = rows.map(sale => `
            <tr class="hover:bg-gray-50 transition">
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
    } finally {
        salesHistoryRefreshInFlight = false;
    }
}

if (fuel) fuel.addEventListener("change", compute);
if (amount) amount.addEventListener("input", compute);
if (printReceiptBtn) printReceiptBtn.addEventListener("click", printLastReceipt);

if (posForm) {
    posForm.addEventListener("submit", async function(e) {
        e.preventDefault();

        const selectedOption = fuel?.options?.[fuel.selectedIndex] || null;
        const rawFuelId = selectedOption ? String(selectedOption.value ?? "") : String(fuel?.value ?? "");
        const fuelName = String(selectedOption?.getAttribute("data-name") || "").trim();
        const priceValue = Number(selectedOption?.getAttribute("data-price") || 0);
        const amountValue = Number(amount?.value || 0);
        const litersValue = priceValue > 0 ? (amountValue / priceValue) : 0;

        if (rawFuelId.trim() === "") {
            showMessage("Please select a fuel product first.", false);
            return;
        }

        if (amountValue <= 0) {
            showMessage("Please enter amount greater than 0.", false);
            return;
        }

        if (priceValue <= 0 || litersValue <= 0 || !Number.isFinite(litersValue)) {
            showMessage("Invalid fuel price. Please reselect fuel product.", false);
            return;
        }

        const formData = new FormData();
        formData.append("fuel_id", rawFuelId.trim());
        formData.append("fuel_name", fuelName);
        formData.append("liters", litersValue.toFixed(4));
        formData.append("amount", amountValue.toFixed(2));
        formData.append("pay", "1");

        try {
            const response = await fetch('/petron_system/public/staff/ajax/process_sale.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showMessage(data.message, true);
                if (data.receipt) {
                    lastReceiptData = data.receipt;
                    setPrintReceiptButtonState(true);
                }

                const selectedFuelId = fuel?.value || null;

                posForm.reset();
                if (total) total.innerText = "0.00";
                if (sumName) sumName.innerText = "-";
                if (sumLiters) sumLiters.innerText = "0.00 L";
                if (sumAmount) sumAmount.innerText = "₱ 0.00";
                if (liters) liters.value = "";

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

async function refreshPosLiveData() {
    if (document.hidden) return;
    await Promise.all([refreshPosFuels(), refreshSalesHistory()]);
}

setPrintReceiptButtonState(false);
compute();
refreshPosLiveData();
setInterval(refreshPosLiveData, POS_AUTO_REFRESH_MS);
document.addEventListener("visibilitychange", function () {
    if (!document.hidden) {
        refreshPosLiveData();
    }
});
