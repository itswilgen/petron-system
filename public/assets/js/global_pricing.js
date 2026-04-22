document.addEventListener("DOMContentLoaded", function () {
  const buttons = document.querySelectorAll(".apply-global-btn");
  const inputs = document.querySelectorAll(".global-price-input");

  if (!buttons.length && !inputs.length) {
    return;
  }

  function submitRow(row) {
    if (!row) {
      return;
    }

    const form = row.querySelector(".global-price-form");
    const input = row.querySelector(".global-price-input");
    const button = row.querySelector(".apply-global-btn");
    const fuelName = button?.getAttribute("data-fuel-name") || "this fuel";

    if (!form || !input) {
      return;
    }

    const price = Number(input.value || 0);
    if (!Number.isFinite(price) || price < 0) {
      alert("Please enter a valid price.");
      input.focus();
      return;
    }

    const confirmText = `Apply ₱${price.toFixed(2)}/L for ${fuelName} in all branches?`;
    if (!window.confirm(confirmText)) {
      return;
    }

    form.submit();
  }

  buttons.forEach((button) => {
    button.addEventListener("click", function () {
      const row = button.closest("tr");
      submitRow(row);
    });
  });

  inputs.forEach((input) => {
    input.addEventListener("change", function () {
      const row = input.closest("tr");
      submitRow(row);
    });
  });
});
