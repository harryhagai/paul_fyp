/**
 * Product Show Page JavaScript
 * Handles all interactive functionality for product display pages
 */

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
        customClass: { ...optionCustomClass, ...baseClass },
        ...restOptions,
    });
}

function setShowButtonLoading(button, isLoading) {
    if (!button) return;
    const spinner = button.querySelector(".btn-dot-spinner");
    const text = button.querySelector(".button-text");

    if (isLoading) {
        button.classList.add("loading");
        button.disabled = true;
        if (spinner) spinner.classList.remove("d-none");
        if (text) text.style.display = "none";
    } else {
        button.classList.remove("loading");
        button.disabled = false;
        if (spinner) spinner.classList.add("d-none");
        if (text) text.style.display = "";
    }
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

/**
 * Show alert notification
 */
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll(".alert-custom");
    existingAlerts.forEach((alert) => alert.remove());

    // Create new alert
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${
        type === "success" ? "success" : "danger"
    } alert-custom alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText =
        "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    alertDiv.innerHTML = `
        <i class="bi ${
            type === "success" ? "bi-check-circle" : "bi-exclamation-triangle"
        }"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

    // Add to page and setup auto-dismiss
    document.body.appendChild(alertDiv);

    if (typeof bootstrap !== "undefined") {
        new bootstrap.Alert(alertDiv);
    }

    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

/**
 * Check if user is authenticated
 */
function isUserAuthenticated() {
    return (
        document.querySelector('a[href*="login"]') === null &&
        document.querySelector('a[href*="logout"], form[action*="logout"]') !==
            null
    );
}

/**
 * Get CSRF token
 */
function getCsrfToken() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    return tokenMeta ? tokenMeta.getAttribute("content") : "";
}

function getCurrentUserRole() {
    return (window.currentUserRole || "").toLowerCase();
}

function isCurrentUserEmailVerified() {
    return window.currentUserEmailVerified === true;
}

function buildAuthRedirectUrl(basePath, params = {}) {
    const url = new URL(basePath, window.location.origin);
    Object.entries(params).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== "") {
            url.searchParams.set(key, String(value));
        }
    });
    return url.pathname + url.search;
}

function buildCurrentPathWithParams(params = {}) {
    const url = new URL(window.location.href);
    Object.entries(params).forEach(([key, value]) => {
        if (value === null || value === undefined || value === "") {
            url.searchParams.delete(key);
        } else {
            url.searchParams.set(key, String(value));
        }
    });
    return url.pathname + url.search;
}

/**
 * Update stock status display
 */
function updateStockStatus(selectedQuantity, totalStock) {
    const stockInfo = document.querySelector(".stock-info");
    if (!stockInfo) return;

    const stockText = stockInfo.querySelector("span:last-child");
    const remainingStock = totalStock - selectedQuantity;

    // Remove existing classes
    stockInfo.classList.remove("stock-in", "stock-low", "stock-out");

    if (remainingStock > 10) {
        stockInfo.classList.add("stock-in");
        stockText.textContent = `In Stock (${remainingStock} available)`;
    } else if (remainingStock > 0) {
        stockInfo.classList.add("stock-low");
        stockText.textContent = `Only ${remainingStock} left in stock`;
    } else {
        stockInfo.classList.add("stock-out");
        stockText.textContent = "Out of Stock";
    }
}

// =====================================================
// IMAGE GALLERY FUNCTIONS
// =====================================================

const galleryState = {
    images: [],
    currentIndex: 0,
    touchStartX: 0,
    touchStartY: 0,
    touchMoved: false,
    wheelLocked: false,
};

function getGalleryIndexBySrc(src) {
    return galleryState.images.findIndex((imageSrc) => imageSrc === src);
}

function setGalleryNavState() {
    const prevBtn = document.querySelector(".gallery-nav-prev");
    const nextBtn = document.querySelector(".gallery-nav-next");
    if (!prevBtn || !nextBtn) return;

    const hasMultiple = galleryState.images.length > 1;
    prevBtn.disabled = !hasMultiple || galleryState.currentIndex <= 0;
    nextBtn.disabled =
        !hasMultiple || galleryState.currentIndex >= galleryState.images.length - 1;
}

function navigateGallery(direction) {
    if (galleryState.images.length <= 1) return;
    const nextIndex = galleryState.currentIndex + direction;
    if (nextIndex < 0 || nextIndex >= galleryState.images.length) return;

    const targetSrc = galleryState.images[nextIndex];
    window.selectImageBySrc(targetSrc, direction);
}

function smoothUpdateMainImage(mainImage, src, zoomType = "out") {
    if (!mainImage || !src || mainImage.src === src) return;

    const applySwap = () => {
        mainImage.classList.remove("is-zoom-in", "is-zoom-out");
        void mainImage.offsetWidth;
        mainImage.classList.add(zoomType === "in" ? "is-zoom-in" : "is-zoom-out");
        mainImage.src = src;
        window.setTimeout(() => {
            mainImage.classList.remove("is-zoom-in", "is-zoom-out");
        }, 460);
    };

    const preloaded = new Image();
    preloaded.src = src;
    if (preloaded.complete) {
        applySwap();
    } else {
        preloaded.onload = applySwap;
        preloaded.onerror = () => {
            mainImage.src = src;
            mainImage.classList.remove("is-zoom-in", "is-zoom-out");
        };
    }
}

function initImageGalleryNavigation() {
    const mainImageFrame = document.getElementById("mainImageFrame");
    if (!mainImageFrame) return;

    const thumbElements = Array.from(
        document.querySelectorAll(".thumb-image, .thumb-image-horizontal")
    );
    const uniqueImages = [
        ...new Set(
            thumbElements
                .map((thumb) => thumb.dataset.imageSrc)
                .filter((src) => typeof src === "string" && src.length > 0)
        ),
    ];

    if (!uniqueImages.length) return;

    galleryState.images = uniqueImages;
    const mainImage = document.getElementById("mainImage");
    const initialSrc = mainImage?.getAttribute("src") || uniqueImages[0];
    const initialIndex = getGalleryIndexBySrc(initialSrc);
    galleryState.currentIndex = initialIndex >= 0 ? initialIndex : 0;
    setGalleryNavState();

    const swipeThreshold = 45;

    mainImageFrame.addEventListener(
        "touchstart",
        (event) => {
            if (!event.touches || event.touches.length !== 1) return;
            galleryState.touchStartX = event.touches[0].clientX;
            galleryState.touchStartY = event.touches[0].clientY;
            galleryState.touchMoved = false;
        },
        { passive: true }
    );

    mainImageFrame.addEventListener(
        "touchmove",
        (event) => {
            if (!event.touches || event.touches.length !== 1) return;
            const deltaX = event.touches[0].clientX - galleryState.touchStartX;
            const deltaY = event.touches[0].clientY - galleryState.touchStartY;
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 8) {
                galleryState.touchMoved = true;
            }
        },
        { passive: true }
    );

    mainImageFrame.addEventListener(
        "touchend",
        (event) => {
            if (!galleryState.touchMoved || !event.changedTouches?.length) return;

            const deltaX = event.changedTouches[0].clientX - galleryState.touchStartX;
            const deltaY = event.changedTouches[0].clientY - galleryState.touchStartY;
            if (Math.abs(deltaX) <= Math.abs(deltaY)) return;
            if (Math.abs(deltaX) < swipeThreshold) return;

            if (deltaX < 0) {
                navigateGallery(1);
            } else {
                navigateGallery(-1);
            }
        },
        { passive: true }
    );

    mainImageFrame.addEventListener(
        "wheel",
        (event) => {
            if (galleryState.images.length <= 1) return;
            const scrollDelta =
                Math.abs(event.deltaX) > Math.abs(event.deltaY)
                    ? event.deltaX
                    : event.deltaY;

            if (Math.abs(scrollDelta) < 20 || galleryState.wheelLocked) return;
            event.preventDefault();
            galleryState.wheelLocked = true;

            if (scrollDelta > 0) {
                navigateGallery(1);
            } else {
                navigateGallery(-1);
            }

            window.setTimeout(() => {
                galleryState.wheelLocked = false;
            }, 220);
        },
        { passive: false }
    );
}

/**
 * Change main product image
 */
window.changeImage = function (src, element, direction = 0) {
    const previousIndex = galleryState.currentIndex;
    const foundIndex = getGalleryIndexBySrc(src);
    let resolvedDirection = Number(direction) || 0;
    if (!resolvedDirection && foundIndex >= 0 && foundIndex !== previousIndex) {
        resolvedDirection = foundIndex > previousIndex ? 1 : -1;
    }

    const zoomType = resolvedDirection < 0 ? "in" : "out";
    const mainImage = document.getElementById("mainImage");
    if (mainImage) smoothUpdateMainImage(mainImage, src, zoomType);

    if (foundIndex >= 0) {
        galleryState.currentIndex = foundIndex;
    }

    // Update active thumbnail states
    document
        .querySelectorAll(".thumb-image, .thumb-image-horizontal")
        .forEach((thumb) => {
            thumb.classList.remove("active");
        });

    if (element) {
        element.classList.add("active");
        element.scrollIntoView({
            behavior: "smooth",
            block: "nearest",
            inline: "nearest",
        });
    }

    document.querySelectorAll(".gallery-dot").forEach((dot) => {
        dot.classList.toggle("active", dot.dataset.imageSrc === src);
    });

    setGalleryNavState();
};

window.selectImageBySrc = function (src, direction = 0) {
    const matchingThumb =
        document.querySelector(`.thumb-image[data-image-src="${src}"]`) ||
        document.querySelector(`.thumb-image-horizontal[data-image-src="${src}"]`);

    window.changeImage(src, matchingThumb || null, direction);
};

window.galleryNext = function () {
    navigateGallery(1);
};

window.galleryPrev = function () {
    navigateGallery(-1);
};

// =====================================================
// QUANTITY CONTROL FUNCTIONS
// =====================================================

/**
 * Change product quantity
 */
window.changeQuantity = function (change) {
    const input = document.getElementById("quantityInput");
    if (!input) return;

    const currentValue = parseInt(input.value) || 1;
    const min = parseInt(input.min) || 1;
    const max = parseInt(input.max) || 99;

    let newValue = currentValue + change;

    if (newValue >= min && newValue <= max) {
        input.value = newValue;
        updateStockStatus(newValue, max);
    } else if (change > 0 && newValue > max) {
        // Show stock limit warning
        fireStyled({
            title: "Stock Limit Reached!",
            text: `Maximum stock available is ${max}`,
            icon: "warning",
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText: "OK",
        });
        input.value = max;
        updateStockStatus(max, max);
    }
};

// =====================================================
// TAB SWITCHING FUNCTIONS
// =====================================================

/**
 * Switch between product tabs
 */
window.switchTab = function (tabId) {
    // Update tab buttons
    document.querySelectorAll(".tab-btn").forEach((btn) => {
        btn.classList.remove("active");
    });

    // Update tab content
    document.querySelectorAll(".tab-content").forEach((content) => {
        content.classList.remove("active");
    });

    // Activate selected tab
    const targetTab = document.getElementById(tabId);
    const targetBtn = event.target.closest(".tab-btn");

    if (targetTab) targetTab.classList.add("active");
    if (targetBtn) targetBtn.classList.add("active");
};

// =====================================================
// CART FUNCTIONS
// =====================================================

/**
 * Add product to cart
 */
window.addToCart = async function (productId, quantity) {
    let button = null;

    try {
        const quantityInput = document.getElementById("quantityInput");
        const maxStock = quantityInput ? parseInt(quantityInput.max || "0", 10) : 0;
        const requestedQty = parseInt(quantity, 10) || 0;

        if (maxStock <= 0) {
            await fireStyled({
                title: "Out of Stock",
                text: "This product is currently out of stock and cannot be added to cart.",
                icon: "warning",
                iconColor: "#dc3545",
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: "OK",
            });
            return;
        }

        if (requestedQty < 1 || requestedQty > maxStock) {
            await fireStyled({
                title: "Stock Limit Reached",
                text: `Available stock is ${maxStock}. Please choose a valid quantity.`,
                icon: "warning",
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: "OK",
            });
            return;
        }

        // Check authentication
        if (!isUserAuthenticated()) {
            const result = await fireStyled({
                title: "Account Required",
                html: `
                    Sorry Dear Customer! <br><br>
                    <strong>You need an account to add items to your cart and complete purchases.</strong><br><br>
                    If you already have an account, please login.<br>
                    If you don't have an account yet, please register first.
                `,
                icon: "info",
                showCancelButton: true,
                showCloseButton: true,
                confirmButtonColor: "#007bff",
                cancelButtonColor: "#28a745",
                confirmButtonText: '<i class="bi bi-person-circle"></i> Login',
                cancelButtonText: '<i class="bi bi-person-plus"></i> Register',
                customClass: {
                    popup: "swal-wide",
                    confirmButton: "btn btn-primary me-5",
                    cancelButton: "btn btn-success",
                },
                buttonsStyling: false,
                reverseButtons: true,
            });

            if (result.isConfirmed) {
                const redirectPath =
                    window.location.pathname + window.location.search;
                window.location.href = buildAuthRedirectUrl("/login", {
                    redirect: redirectPath,
                    action: "add_to_cart",
                    product_id: productId,
                    quantity: quantity,
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = buildAuthRedirectUrl("/register", {
                    redirect: window.location.pathname + window.location.search,
                    action: "add_to_cart",
                    product_id: productId,
                    quantity: quantity,
                });
            }
            return;
        }

        const currentRole = getCurrentUserRole();
        if (currentRole && currentRole !== "customer") {
            await fireStyled({
                title: "Customer Access Only",
                text: "Add to cart is available for customer accounts only.",
                icon: "info",
                confirmButtonText:
                    '<i class="bi bi-speedometer2 me-1"></i>Go to Dashboard',
                showCloseButton: true,
            });

            window.location.href =
                window.currentUserDashboardUrl || "/login";
            return;
        }

        if (!isCurrentUserEmailVerified()) {
            window.location.href = buildAuthRedirectUrl("/email/verify", {
                redirect: window.location.pathname + window.location.search,
                action: "add_to_cart",
                product_id: productId,
                quantity: quantity,
            });
            return;
        }

        // Prepare request
        button = event.target.closest(".btn-add-cart");
        if (!button) return;

        setShowButtonLoading(button, true);

        const formData = new FormData();
        formData.append("product_id", productId);
        formData.append("quantity", quantity);
        formData.append("_token", getCsrfToken());

        // Make request
        const response = await fetch("/cart/add", {
            method: "POST",
            body: formData,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
        });

        // Handle CSRF expiry
        if (response.status === 419) {
            const result = await fireStyled({
                title: "Session Expired",
                text: "Please refresh the page and try again",
                icon: "warning",
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: "Refresh Page",
            });

            if (result.isConfirmed) {
                window.location.reload();
            }
            return;
        }

        const data = await response.json();

        // Update cart count
        const cartCountElements = document.querySelectorAll(".cart-count");
        cartCountElements.forEach((element) => {
            if (element) {
                element.textContent = data.cart_count || 0;
                element.style.display = data.cart_count > 0 ? "inline" : "none";
            }
        });

        // Handle response
        if (data.success) {
            const result = await fireStyled({
                title: "Item Added to Cart!",
                text: data.message,
                icon: "success",
                color: "#1f2937",
                background: "#ffffff",
                confirmButtonColor: THEME_PRIMARY,
                cancelButtonColor: "#6b7280",
                confirmButtonText: '<i class="bi bi-cart3 me-1"></i>View Cart',
                showCancelButton: true,
                cancelButtonText:
                    '<i class="bi bi-arrow-left-right me-1"></i>Continue Shopping',
                showCloseButton: true,
                timer: 3500,
                timerProgressBar: true,
                customClass: {
                    popup: "swal-wide",
                    confirmButton: "btn btn-primary me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
                buttonsStyling: false,
                didOpen: (popup) => {
                    const progressBar = popup.querySelector(
                        ".swal2-timer-progress-bar"
                    );
                    if (progressBar) {
                        progressBar.style.setProperty(
                            "--swal2-timer-progress-bar-background",
                            THEME_PRIMARY
                        );
                    }
                },
            });

            if (result.isConfirmed) {
                window.location.href = "/cart";
            }
        } else if (data.already_in_cart) {
            const result = await fireStyled({
                title: "Already in Cart",
                text: data.message,
                icon: "info",
                color: "#1f2937",
                background: "#ffffff",
                confirmButtonColor: THEME_PRIMARY,
                cancelButtonColor: "#6b7280",
                confirmButtonText: '<i class="bi bi-cart3 me-1"></i>View Cart',
                showCancelButton: true,
                cancelButtonText:
                    '<i class="bi bi-arrow-left-right me-1"></i>Continue Shopping',
                showCloseButton: true,
                customClass: {
                    popup: "swal-wide",
                    confirmButton: "btn btn-primary me-2",
                    cancelButton: "btn btn-outline-secondary",
                },
                buttonsStyling: false,
            });

            if (result.isConfirmed) {
                window.location.href = "/cart";
            }
        } else {
            const isOutOfStock = Boolean(data.out_of_stock);
            await fireStyled({
                title: isOutOfStock ? "Out of Stock" : "Oops!",
                text: data.message || "Failed to add item to cart",
                icon: isOutOfStock ? "warning" : "error",
                iconColor: isOutOfStock ? "#dc3545" : undefined,
                color: "#1f2937",
                background: "#ffffff",
                confirmButtonColor: THEME_PRIMARY,
                confirmButtonText: "Try Again",
                showCloseButton: true,
                customClass: {
                    popup: "swal-wide",
                    confirmButton: "btn btn-primary",
                },
                buttonsStyling: false,
            });
        }
    } catch (error) {
        console.error("Error adding to cart:", error);
        await fireStyled({
            title: "Error",
            text: "An error occurred. Please try again.",
            icon: "error",
            confirmButtonColor: THEME_PRIMARY,
        });
    } finally {
        // Reset button
        if (button) {
            setShowButtonLoading(button, false);
        }
    }
};

// Product rating logic is maintained in show-rating.js.

// CSS-Only Carousel and Rating Functionality
document.addEventListener("DOMContentLoaded", function () {
    initImageGalleryNavigation();

    document.querySelectorAll(".show-dot-btn[data-spin-link='1']").forEach((link) => {
        link.addEventListener("click", function () {
            this.classList.add("loading");
            const text = this.querySelector(".button-text");
            if (text) text.style.opacity = "0.92";
        });
    });

    const carousel = document.querySelector(".css-carousel");
    const indicators = document.querySelectorAll(".css-indicator");
    let currentGroupIndex = 0;
    let autoScrollInterval;

    // Calculate total groups (3 dots)
    const totalSlides = document.querySelectorAll(".css-carousel-slide").length;
    const slidesPerGroup = 3;
    const totalGroups = Math.ceil(totalSlides / slidesPerGroup);

    // Function to scroll to a specific group
    function scrollToGroup(groupIndex) {
        if (!carousel) return;

        const slideWidth = 300 + 20; // 300px width + 20px margin
        const scrollPosition = groupIndex * slideWidth * slidesPerGroup;

        carousel.scrollTo({
            left: scrollPosition,
            behavior: "smooth",
        });

        // Update indicators
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle("active", i === groupIndex);
        });

        currentGroupIndex = groupIndex;
    }

    // Auto scroll function
    function autoScroll() {
        currentGroupIndex = (currentGroupIndex + 1) % totalGroups;
        scrollToGroup(currentGroupIndex);
    }

    // Start auto scrolling
    function startAutoScroll() {
        autoScrollInterval = setInterval(autoScroll, 3000); // 3 seconds
    }

    // Stop auto scrolling
    function stopAutoScroll() {
        if (autoScrollInterval) {
            clearInterval(autoScrollInterval);
            autoScrollInterval = null;
        }
    }

    // Handle indicator clicks
    indicators.forEach((indicator, index) => {
        indicator.addEventListener("click", function () {
            stopAutoScroll(); // Pause auto scroll when user interacts
            scrollToGroup(index);

            // Resume auto scroll after 5 seconds of inactivity
            setTimeout(() => {
                if (!autoScrollInterval) {
                    startAutoScroll();
                }
            }, 5000);
        });
    });

    // Handle manual scrolling
    let scrollTimeout;
    carousel?.addEventListener("scroll", function () {
        stopAutoScroll();

        // Clear existing timeout
        clearTimeout(scrollTimeout);

        // Resume auto scroll after user stops scrolling
        scrollTimeout = setTimeout(() => {
            if (!autoScrollInterval) {
                startAutoScroll();
            }
        }, 3000);
    });

    // Pause on hover
    carousel?.addEventListener("mouseenter", stopAutoScroll);
    carousel?.addEventListener("mouseleave", startAutoScroll);

    // Start auto scrolling initially
    startAutoScroll();

    // Rating UI logic is initialized in show-rating.js.
});

document.addEventListener("DOMContentLoaded", function () {
    if (!window.productId) return;
    const endpoint = window.productViewActivityUrl;
    if (!endpoint) return;

    const csrfToken = getCsrfToken();
    const MIN_TRACK_SECONDS = 10;
    const startedAt = Date.now();
    let heartbeat = null;

    const pushActivity = (eventType, useBeacon = false) => {
        const seconds = Math.max(1, Math.floor((Date.now() - startedAt) / 1000));
        const payload = { event: eventType, duration_seconds: seconds };

        if (useBeacon && navigator.sendBeacon) {
            const data = new FormData();
            data.append("_token", csrfToken);
            data.append("event", eventType);
            data.append("duration_seconds", String(seconds));
            navigator.sendBeacon(endpoint, data);
            return;
        }

        fetch(endpoint, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            body: JSON.stringify(payload),
        }).catch(() => {});
    };

    heartbeat = setInterval(() => {
        const seconds = Math.max(1, Math.floor((Date.now() - startedAt) / 1000));
        if (seconds >= MIN_TRACK_SECONDS) {
            pushActivity("heartbeat");
        }
    }, 15000);

    window.addEventListener("beforeunload", function () {
        if (heartbeat) clearInterval(heartbeat);
        const seconds = Math.max(1, Math.floor((Date.now() - startedAt) / 1000));
        if (seconds >= MIN_TRACK_SECONDS) {
            pushActivity("view_end", true);
        }
    });
});

// Notification function
function showNotification(message, type = "success") {
    const notification = document.getElementById("notification");
    const messageEl = document.getElementById("notification-message");
    const iconEl = notification.querySelector("i");

    messageEl.textContent = message;

    if (type === "error") {
        notification.style.background = "var(--danger-color)";
        iconEl.className = "bi bi-exclamation-triangle";
    } else {
        notification.style.background = "var(--success-color)";
        iconEl.className = "bi bi-check-circle";
    }

    notification.style.display = "block";
    setTimeout(() => {
        notification.classList.add("show");
    }, 10);

    setTimeout(() => {
        notification.classList.remove("show");
        setTimeout(() => {
            notification.style.display = "none";
        }, 300);
    }, 3000);
}
