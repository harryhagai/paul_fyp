

// Helper function to format numbers with commas
function formatNumber(num) {
    return parseFloat(num).toLocaleString('en', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

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

// Touch-friendly quantity updates
function updateQuantity(cartItemId, change) {
    const cartItem = event.target.closest(".cart-item-card");
    const input = cartItem.querySelector(".quantity-input");
    const currentValue = parseInt(input.value);
    const newValue = currentValue + change;

    if (newValue >= 1 && newValue <= 99) {
        updateQuantityDirectly(cartItemId, newValue, event.target);
    }
}

function updateQuantityDirectly(cartItemId, quantity, buttonElement) {
    const cartItem = document.querySelector(
        `.cart-item-card[data-cart-item-id="${cartItemId}"]`
    );
    const stock = parseInt(cartItem.dataset.stock);
    if (quantity > stock) {
        fireStyled({
            title: "Stock Limit Reached!",
            text: `Maximum stock available is ${stock}`,
            icon: "warning",
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText: "OK",
        });
        const input = cartItem.querySelector(".quantity-input");
        input.value = stock;
        return;
    }

    const button = buttonElement ? buttonElement.closest(".quantity-btn") : null;
    if (button) button.disabled = true;

    fetch(`/cart/update/${cartItemId}`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({ quantity: quantity }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Update UI without refreshing
                updateCartUI(cartItemId, quantity);
                if (button) button.disabled = false;
            } else {
                fireStyled({
                    title: "Update Failed",
                    text: data.message || "Failed to update quantity",
                    icon: "error",
                    confirmButtonColor: THEME_PRIMARY,
                    confirmButtonText: "OK",
                });
                if (button) button.disabled = false;
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            fireStyled({
                title: "Error!",
                text: "An error occurred. Please try again.",
                icon: "error",
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: "OK",
            });
            if (button) button.disabled = false;
        });
}

function validateAndUpdateQuantity(input, cartItemId, stock) {
    let value = parseInt(input.value);
    if (isNaN(value) || value < 1) {
        value = 1;
        input.value = value;
    } else if (value > stock) {
        value = stock;
        input.value = value;
        fireStyled({
            title: "Stock Limit Reached!",
            text: `Maximum stock available is ${stock}`,
            icon: "warning",
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText: "OK",
        });
    }
    updateQuantityDirectly(cartItemId, value);
}

function previewQuantityChange(input) {
    const cartItem = input.closest(".cart-item-card");
    if (!cartItem) return;

    let value = parseInt(input.value, 10);
    const stock = parseInt(cartItem.dataset.stock, 10);
    if (isNaN(value) || value < 1) value = 1;
    if (!isNaN(stock) && value >= stock) {
        value = stock;
        if (input.dataset.maxWarned !== "1") {
            fireStyled({
                title: "Stock Limit Reached!",
                text: `Maximum stock available is ${stock}`,
                icon: "warning",
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: "OK",
            });
            input.dataset.maxWarned = "1";
        }
    } else {
        input.dataset.maxWarned = "0";
    }

    if (parseInt(input.value, 10) !== value) {
        input.value = value;
    }

    const price = parseFloat(cartItem.dataset.price);
    const newTotal = price * value;
    const totalElement = cartItem.querySelector(".cart-item-total-value");
    if (totalElement) totalElement.textContent = "Total : Tsh" + formatNumber(newTotal.toFixed(2));

    calculateTotals();
}

function updateCartUI(updatedItemId, newQuantity) {
    // Update the quantity input
    const cartItem = document.querySelector(
        `.cart-item-card[data-cart-item-id="${updatedItemId}"]`
    );
    if (cartItem) {
        const quantityInput = cartItem.querySelector(".quantity-input");
        quantityInput.value = newQuantity;

        // Update item total
        const price = parseFloat(cartItem.dataset.price);
        const newTotal = price * newQuantity;
        const totalElement = cartItem.querySelector(".cart-item-total-value");
        if (totalElement) totalElement.textContent = "Total : Tsh" + formatNumber(newTotal.toFixed(2));

        // Recalculate totals
        calculateTotals();
    }
}

function removeItem(cartItemId) {
    fireStyled({
        title: "Remove Item?",
        text: "Are you sure you want to remove this item from your cart?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: THEME_PRIMARY,
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Delete',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
            confirmButton: "btn btn-outline-danger",
            cancelButton: "btn btn-outline-secondary",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading spinner
            const removeBtn = document.querySelector(
                `button[data-item-id="${cartItemId}"]`
            );
            if (removeBtn) {
                removeBtn.disabled = true;
                removeBtn.classList.add("loading");
                removeBtn
                    .querySelector(".btn-dot-spinner")
                    .classList.remove("d-none");
                removeBtn.querySelector(".button-text").style.display = "none";
            }

            fetch(`/cart/remove/${cartItemId}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // Remove item from UI
                        const cartItem = document.querySelector(
                            `.cart-item-card[data-cart-item-id="${cartItemId}"]`
                        );
                        if (cartItem) {
                            cartItem.remove();
                        }

                        // Check if cart is now empty
                        const remainingItems = document.querySelectorAll(
                            ".cart-item-card[data-product-id]"
                        );
                        if (remainingItems.length === 0) {
                            // Cart is empty, reload to show empty cart state
                            fireStyled({
                                title: "Cart is Empty!",
                                text: "All items have been removed from your cart.",
                                icon: "info",
                                confirmButtonColor: THEME_PRIMARY,
                                timer: 2000,
                                showConfirmButton: false,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            // Cart still has items, just recalculate totals
                            calculateTotals();
                            fireStyled({
                                title: "Removed!",
                                text: "Item has been removed from your cart.",
                                icon: "success",
                                confirmButtonColor: THEME_PRIMARY,
                                timer: 2000,
                                showConfirmButton: false,
                            });
                        }
                    } else {
                        fireStyled({
                            title: "Failed to Remove",
                            text:
                                data.message ||
                                "Failed to remove item from cart",
                            icon: "error",
                            confirmButtonColor: THEME_PRIMARY,
                            confirmButtonText: "OK",
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    fireStyled({
                        title: "Error!",
                        text: "An error occurred while removing the item.",
                        icon: "error",
                        confirmButtonColor: THEME_PRIMARY,
                        confirmButtonText: "OK",
                    });
                })
                .finally(() => {
                    // Hide loading spinner
                    const removeBtn = document.querySelector(
                        `button[data-item-id="${cartItemId}"]`
                    );
                    if (removeBtn) {
                        removeBtn.disabled = false;
                        removeBtn.classList.remove("loading");
                        removeBtn
                            .querySelector(".btn-dot-spinner")
                            .classList.add("d-none");
                        removeBtn.querySelector(".button-text").style.display =
                            "inline";
                    }
                });
        }
    });
}

function clearCart(button) {
    fireStyled({
        title: "Clear Entire Cart?",
        text: "Are you sure you want to remove all items from your cart? This action cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: THEME_PRIMARY,
        confirmButtonText: '<i class="bi bi-trash me-1"></i>Clear All',
        cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
        customClass: {
            confirmButton: "btn btn-outline-danger",
            cancelButton: "btn btn-outline-secondary",
        },
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading spinner
            const clearBtn = button;
            if (clearBtn) {
                clearBtn.disabled = true;
                clearBtn.classList.add("loading");
                clearBtn
                    .querySelector(".btn-dot-spinner")
                    .classList.remove("d-none");
                clearBtn.querySelector(".button-text").style.display = "none";
            }

            fetch("/cart/clear", {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        fireStyled({
                            title: "Cart Cleared!",
                            text: "All items have been removed from your cart.",
                            icon: "success",
                            confirmButtonColor: THEME_PRIMARY,
                            timer: 2500,
                            showConfirmButton: false,
                        }).then(() => {
                            // Reload the page to show empty cart
                            location.reload();
                        });
                    } else {
                        fireStyled({
                            title: "Failed to Clear Cart",
                            text: data.message || "Failed to clear cart",
                            icon: "error",
                            confirmButtonColor: THEME_PRIMARY,
                            confirmButtonText: "OK",
                        });
                    }
                })
                .catch((error) => {
                    console.error("Error:", error);
                    fireStyled({
                        title: "Error!",
                        text: "An error occurred while clearing your cart.",
                        icon: "error",
                        confirmButtonColor: THEME_PRIMARY,
                        confirmButtonText: "OK",
                    });
                })
                .finally(() => {
                    // Hide loading spinner
                    const clearBtn = button;
                    if (clearBtn) {
                        clearBtn.disabled = false;
                        clearBtn.classList.remove("loading");
                        clearBtn
                            .querySelector(".btn-dot-spinner")
                            .classList.add("d-none");
                        clearBtn.querySelector(".button-text").style.display =
                            "inline";
                    }
                });
        }
    });
}

function calculateTotals() {
    let subtotal = 0;
    const cartItems = document.querySelectorAll(".cart-item-card[data-product-id]");
    let itemCount = 0;

    cartItems.forEach((item) => {
        const quantity = parseInt(item.querySelector(".quantity-input").value);
        const price = parseFloat(item.dataset.price);
        subtotal += price * quantity;
        itemCount += quantity;
    });

    const total = subtotal;

    // Update UI with null checks (cart summary elements don't exist when cart is empty)
    const subtotalElement = document.querySelector(".cart-summary-subtotal");
    const subtotalAmountElement = document.querySelector(
        ".cart-summary-subtotal-amount"
    );
    const totalElement = document.querySelector(".cart-summary-total");

    if (subtotalElement)
        subtotalElement.textContent = "Subtotal (" + itemCount + " items)";
    if (subtotalAmountElement)
        subtotalAmountElement.textContent = "Tsh" + formatNumber(subtotal.toFixed(2));
    if (totalElement) totalElement.textContent = "Tsh" + formatNumber(total.toFixed(2));

}

// Make buttons more touch-friendly
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".quantity-btn, .btn-remove, .clear-cart-btn");
    buttons.forEach((button) => {
        button.style.cursor = "pointer";
        button.addEventListener("touchstart", function () {
            this.style.opacity = "0.7";
        });
        button.addEventListener("touchend", function () {
            this.style.opacity = "1";
        });
    });

    document.querySelectorAll(".cart-dot-btn[data-spin-link='1']").forEach((link) => {
        link.addEventListener("click", function () {
            this.classList.add("loading");
            const text = this.querySelector(".button-text");
            if (text) text.style.opacity = "0.92";
        });
    });

    // Calculate totals on page load
    calculateTotals();
});
