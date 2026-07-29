(() => {
    const page = document.querySelector(".customer-order-details-page");
    if (!page) return;

    const orderId = page.dataset.orderId;
    const payUrl = page.dataset.payUrl;
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    if (!orderId || !csrfToken) return;

    const cancelOrderBtn = document.getElementById("cancelOrderBtn");
    const payOrderBtn = document.getElementById("payOrderBtn");
    const cancelOrderForm = document.querySelector("#cancelOrderModal form");
    const THEME_PRIMARY =
        getComputedStyle(document.documentElement)
            .getPropertyValue("--teal-primary")
            .trim() || "#0d9488";

    function fireStyled(options = {}) {
        const { customClass: optionCustomClass = {}, ...restOptions } = options;
        const baseClass = {
            popup: "category-delete-popup",
            icon: "category-delete-icon",
            title: "category-delete-title",
            htmlContainer: "category-delete-text",
            actions: "category-delete-actions",
            confirmButton: "category-delete-confirm btn",
            cancelButton: "category-delete-cancel btn",
        };

        return Swal.fire({
            buttonsStyling: false,
            customClass: { ...baseClass, ...optionCustomClass },
            ...restOptions,
        });
    }

    function showButtonLoading(button) {
        if (!button) return;
        const spinner = button.querySelector(".spinner-border, .btn-dot-spinner");
        const text = button.querySelector(".btn-text");
        if (spinner) spinner.classList.remove("d-none");
        if (text) text.classList.add("d-none");
        if ("disabled" in button) button.disabled = true;
        button.classList.add("disabled");
        button.setAttribute("aria-busy", "true");
    }

    function hideButtonLoading(button) {
        if (!button) return;
        const spinner = button.querySelector(".spinner-border, .btn-dot-spinner");
        const text = button.querySelector(".btn-text");
        if (spinner) spinner.classList.add("d-none");
        if (text) text.classList.remove("d-none");
        if ("disabled" in button) button.disabled = false;
        button.classList.remove("disabled");
        button.removeAttribute("aria-busy");
    }

    function showFloatingMessage(message, type) {
        const iconMap = {
            danger: "error",
            error: "error",
            warning: "warning",
            success: "success",
            info: "info",
        };
        const icon = iconMap[type] || "info";
        const autoClose = icon === "success";

        fireStyled({
            title:
                icon === "success"
                    ? "Success"
                    : icon === "error"
                      ? "Error"
                      : "Notice",
            text: message,
            icon,
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText: "OK",
            timer: autoClose ? 2200 : undefined,
            showConfirmButton: !autoClose,
        });
    }

    async function requestOrderJson(url, options = {}) {
        const response = await fetch(url, {
            ...options,
            headers: {
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
                "Content-Type": "application/json",
                ...(options.headers || {}),
            },
        });

        const contentType = response.headers.get("content-type") || "";
        let payload = {};

        if (contentType.includes("application/json")) {
            payload = await response.json();
        } else {
            payload = {
                success: false,
                message: "Unexpected server response.",
            };
        }

        if (!response.ok && !payload.message) {
            payload.message = "Request failed.";
        }

        return payload;
    }

    cancelOrderBtn?.addEventListener("click", async () => {
        if (!cancelOrderForm) return;

        const result = await fireStyled({
            title: "Cancel Order?",
            text: "Are you sure you want to cancel this order? This action cannot be undone.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel Order',
            cancelButtonText:
                '<i class="bi bi-arrow-counterclockwise me-1"></i>Keep Order',
            customClass: {
                confirmButton: "btn btn-outline-danger",
                cancelButton: "btn btn-outline-secondary",
            },
        });

        if (!result.isConfirmed) return;
        cancelOrderBtn.classList.add("disabled");
        cancelOrderBtn.setAttribute("aria-busy", "true");
        cancelOrderForm.submit();
    });

    payOrderBtn?.addEventListener("click", async function () {
        if (!payUrl) return;

        showButtonLoading(this);

        try {
            const data = await requestOrderJson(payUrl, { method: "POST" });

            if (!data.success) {
                showFloatingMessage(data.message || "Failed to send payment prompt.", "danger");
                return;
            }

            showFloatingMessage(
                data.message || "A mobile money payment request has been sent to your phone.",
                "success"
            );
        } catch (error) {
            showFloatingMessage(
                error?.message || "An error occurred while sending the payment prompt.",
                "danger"
            );
        } finally {
            hideButtonLoading(this);
        }
    });

    document.querySelectorAll(".quantity-btn").forEach((button) => {
        button.addEventListener("click", function () {
            const form = this.closest(".update-quantity-form");
            const input = form?.querySelector(".quantity-input");
            if (!form || !input) return;

            const itemId = form.dataset.itemId;
            let quantity = parseInt(input.value, 10);

            if (this.dataset.action === "increase") {
                if (quantity >= parseInt(input.max, 10)) {
                    showFloatingMessage(
                        `Cannot add more items. Maximum stock available: ${input.max}`,
                        "danger"
                    );
                    return;
                }
                quantity++;
            } else if (quantity > 1) {
                quantity--;
            }

            input.value = quantity;
            showButtonLoading(this);
            updateQuantity(itemId, quantity, form, this);
        });
    });

    document.querySelectorAll(".remove-item-btn").forEach((button) => {
        button.addEventListener("click", async function () {
            const itemId = this.dataset.itemId;
            const productName = this.dataset.productName || "this item";
            if (!itemId) return;

            const result = await fireStyled({
                title: "Remove Item?",
                text: `Are you sure you want to remove "${productName}" from this order?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Remove',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Keep Item',
                customClass: {
                    confirmButton: "btn btn-outline-danger",
                    cancelButton: "btn btn-outline-secondary",
                },
            });

            if (!result.isConfirmed) return;
            removeOrderItem(itemId, this);
        });
    });

    document
        .querySelector(".back-to-orders-btn")
        ?.addEventListener("click", function () {
            showButtonLoading(this);
        });

    async function removeOrderItem(itemId, triggerButton) {
        showButtonLoading(triggerButton);

        try {
            const data = await requestOrderJson(
                `/customer/orders/${orderId}/items/${itemId}`,
                { method: "DELETE" }
            );

            if (!data.success) {
                showFloatingMessage(data.message || "Failed to remove item.", "danger");
                return;
            }

            triggerButton.closest("[data-order-item-id]")?.remove();

            if (data.order_total) {
                const orderTotalEl = document.querySelector(".js-order-total");
                if (orderTotalEl) orderTotalEl.textContent = data.order_total;
            }

            if (document.querySelectorAll(".order-item-card").length === 0) {
                location.reload();
                return;
            }

            showFloatingMessage(
                data.message || "Item removed from order successfully.",
                "success"
            );
        } catch (error) {
            showFloatingMessage(
                error?.message || "An error occurred while removing the item.",
                "danger"
            );
        } finally {
            hideButtonLoading(triggerButton);
        }
    }

    async function updateQuantity(itemId, quantity, form, button) {
        try {
            const data = await requestOrderJson(
                `/customer/orders/${orderId}/items/${itemId}`,
                {
                    method: "PATCH",
                    body: JSON.stringify({ quantity }),
                }
            );

            if (!data.success) {
                showFloatingMessage(
                    data.error || data.message || "Failed to update quantity.",
                    "danger"
                );
                return;
            }

            form.closest("[data-order-item-id]").querySelector(".item-total").textContent =
                data.item_total;
            document.querySelector(".js-order-total").textContent = data.order_total;
            showFloatingMessage("Quantity updated successfully.", "success");
        } catch (error) {
            showFloatingMessage(
                error?.message || "An error occurred while updating the quantity.",
                "danger"
            );
        } finally {
            hideButtonLoading(button);
        }
    }
})();
