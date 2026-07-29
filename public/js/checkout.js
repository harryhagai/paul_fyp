// Checkout page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Checkout page loaded');
    const THEME_PRIMARY = getComputedStyle(document.documentElement)
        .getPropertyValue('--teal-primary')
        .trim() || '#0d9488';
    const THEME_SECONDARY = getComputedStyle(document.documentElement)
        .getPropertyValue('--teal-secondary')
        .trim() || '#14b8a6';

    function fireStyled(options = {}) {
        const { customClass: optionCustomClass = {}, ...restOptions } = options;
        const baseClass = {
            popup: 'category-delete-popup',
            icon: 'category-delete-icon',
            title: 'category-delete-title',
            htmlContainer: 'category-delete-text',
            actions: 'category-delete-actions',
            confirmButton: 'category-delete-confirm btn',
            cancelButton: 'category-delete-cancel btn',
        };

        return Swal.fire({
            buttonsStyling: false,
            customClass: { ...baseClass, ...optionCustomClass },
            ...restOptions,
        });
    }

    function parseJson(text) {
        if (!text) return null;

        try {
            return JSON.parse(text);
        } catch (error) {
            return null;
        }
    }

    function stripHtml(text) {
        if (!text) return '';

        const doc = new DOMParser().parseFromString(text, 'text/html');
        return (doc.body.textContent || text).replace(/\s+/g, ' ').trim();
    }

    function formatValidationErrors(errors) {
        if (!errors || typeof errors !== 'object') return '';

        return Object.values(errors).flat().filter(Boolean).join(' ');
    }

    function buildCheckoutErrorMessage(data, fallback = 'We could not place your order right now. Please try again.') {
        const validationMessage = formatValidationErrors(data?.errors);

        if (validationMessage && validationMessage !== data?.message) {
            return `${data?.message || 'Please check your information and try again.'}\n${validationMessage}`;
        }

        if (data?.message) {
            return data.message;
        }

        if (data?.error_code === 'PAYMENT_PROMPT_FAILED') {
            return 'We could not send the mobile money request to your phone. Please check your phone number and try again.';
        }

        return fallback;
    }

    function showCheckoutError(data, title = 'Order Failed') {
        const shouldGoBackToCart = data?.action === 'back_to_cart' || data?.error_code === 'AMOUNT_OUT_OF_RANGE';
        const confirmButtonText = shouldGoBackToCart
            ? '<i class="bi bi-cart3 me-1"></i>Back to Cart'
            : '<i class="bi bi-arrow-repeat me-1"></i>Try Again';

        fireStyled({
            title,
            text: buildCheckoutErrorMessage(data),
            icon: 'error',
            iconColor: '#dc3545',
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText,
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            didOpen: () => {
                const confirmBtn = document.querySelector('.swal2-confirm');
                if (confirmBtn) {
                    confirmBtn.style.backgroundColor = THEME_PRIMARY;
                    confirmBtn.style.borderColor = THEME_PRIMARY;
                    confirmBtn.style.color = '#fff';
                }
            },
        }).then((result) => {
            if (result.isConfirmed && shouldGoBackToCart) {
                window.location.href = data?.cart_url || window.checkoutData.routes.cart;
            }
        });
    }

    function displayPhoneNumber(phone) {
        const digits = String(phone || '').replace(/\D+/g, '');

        if (digits.length === 9) {
            return `+255${digits}`;
        }

        if (digits.length === 12 && digits.startsWith('255')) {
            return `+${digits}`;
        }

        return phone || '';
    }

    // Confirm order placement
    window.confirmOrder = function() {
        console.log('confirmOrder called');
        const placeOrderBtns = [document.getElementById('placeOrderBtn'), document.getElementById('placeOrderBtnMobile')];
        placeOrderBtns.forEach((btn) => setCheckoutButtonLoading(btn, true));

        const form = document.getElementById('checkoutForm');
        if (!form) {
            console.error('Form not found');
            placeOrderBtns.forEach((btn) => setCheckoutButtonLoading(btn, false));
            return;
        }

        // Check if form is valid
        if (!form.checkValidity()) {
            console.log('Form validation failed');
            form.reportValidity();
            placeOrderBtns.forEach((btn) => setCheckoutButtonLoading(btn, false));
            return;
        }

        console.log('Form validation passed');

        // Collect form data
        const formData = new FormData(form);
        const displayPhone = displayPhoneNumber(formData.get('phone_number'));

        // Calculate totals and show confirmation
        const subtotal = window.checkoutData.subtotal;
        const total = window.checkoutData.total;
        const itemCount = window.checkoutData.itemCount;

        console.log('Showing confirmation modal');

        const popupHtml = `
            <style>
                .checkout-confirm-grid {
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 10px;
                    text-align: left;
                    font-size: 0.98rem;
                    font-weight: 400;
                }
                .checkout-confirm-card {
                    padding: 10px 12px;
                    border: 1px solid #e5e7eb;
                    border-radius: 10px;
                }
                .checkout-confirm-title {
                    margin-bottom: 4px;
                    color: ${THEME_PRIMARY};
                    font-weight: 400;
                }
                .checkout-confirm-total {
                    border-top: 1px solid #e5e7eb;
                    margin-top: 8px;
                    padding-top: 8px;
                    display: flex;
                    justify-content: space-between;
                    color: ${THEME_SECONDARY};
                    font-weight: 400;
                }
                @media (min-width: 768px) {
                    .checkout-confirm-grid {
                        grid-template-columns: 1fr 1fr;
                    }
                    .checkout-confirm-summary {
                        grid-column: 1 / -1;
                    }
                }
            </style>
            <div class="checkout-confirm-grid">
                <div class="checkout-confirm-card">
                    <div class="checkout-confirm-title">Your Personal Information</div>
                    <div>Name: ${formData.get('first_name')} ${formData.get('last_name')}</div>
                    <div>Email: ${formData.get('email')}</div>
                    <div>Phone: ${displayPhone}</div>
                </div>
                <div class="checkout-confirm-card checkout-confirm-summary">
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                        <span>Items: ${itemCount}</span>
                        <span>Subtotal: Tsh${subtotal.toLocaleString()}</span>
                    </div>
                    <div class="checkout-confirm-total">
                        <span>Total</span>
                        <span>Tsh${total.toLocaleString()}</span>
                    </div>
                </div>
            </div>
        `;

        fireStyled({
            title: 'Confirm Your Order',
            html: popupHtml,
            icon: 'question',
            width: '760px',
            showCancelButton: true,
            reverseButtons: false,
            confirmButtonColor: THEME_PRIMARY,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i>Confirm Your Order',
            cancelButtonText: '<i class="bi bi-file-earmark-text me-1"></i>Review',
            customClass: {
                confirmButton: 'btn btn-outline-success',
                cancelButton: 'btn btn-outline-secondary',
            },
        }).then((result) => {
            if (result.isConfirmed) {
                placeOrder();
            }
        }).finally(() => {
            placeOrderBtns.forEach((btn) => setCheckoutButtonLoading(btn, false));
        });
    };

    // Submit the order
    window.placeOrder = function() {
        console.log('placeOrder called');

        const form = document.getElementById('checkoutForm');
        const loadingOverlay = document.getElementById('loadingOverlay');

        const confirmBtn = document.getElementById('confirmOrderBtn');
        setCheckoutButtonLoading(confirmBtn, true);

        if (!form || !loadingOverlay) {
            console.error('Form or loading overlay not found');
            setCheckoutButtonLoading(confirmBtn, false);
            return;
        }

        const processingHtml = `
            <style>
                .checkout-processing-wrap {
                    text-align: center;
                    padding: 8px 0 2px;
                }
                .checkout-processing-dots {
                    display: inline-flex;
                    gap: 8px;
                    margin: 10px 0 12px;
                }
                .checkout-processing-dots span {
                    width: 10px;
                    height: 10px;
                    border-radius: 999px;
                    background: ${THEME_PRIMARY};
                    opacity: 0.35;
                    animation: checkoutProcessingPulse 1s infinite ease-in-out;
                }
                .checkout-processing-dots span:nth-child(2) { animation-delay: .15s; }
                .checkout-processing-dots span:nth-child(3) { animation-delay: .3s; }
                @keyframes checkoutProcessingPulse {
                    0%, 80%, 100% { transform: scale(0.8); opacity: 0.35; }
                    40% { transform: scale(1); opacity: 1; }
                }
            </style>
            <div class="checkout-processing-wrap">
                <div class="checkout-processing-dots"><span></span><span></span><span></span></div>
                <p class="mb-0">Placing your order, please wait...</p>
            </div>
        `;
        fireStyled({
            title: 'Processing Order',
            html: processingHtml,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
        });
        console.log('Processing modal shown');

        // Prepare form data for AJAX submission
        const formData = new FormData(form);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        // Make AJAX request instead of form submission
        fetch(window.checkoutData.routes.store, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(async response => {
            if (response.status === 419) {
                // CSRF token expired
                Swal.close();
                fireStyled({
                    title: 'Session Expired',
                    text: 'Please refresh the page and try again',
                    icon: 'warning',
                    iconColor: THEME_PRIMARY,
                    confirmButtonColor: THEME_PRIMARY,
                    confirmButtonText: '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Page'
                }).then(() => {
                    window.location.reload();
                });
                return null;
            }

            const responseText = await response.text();
            const data = parseJson(responseText) || {};

            if (!response.ok) {
                return {
                    success: false,
                    status: response.status,
                    status_text: response.statusText,
                    message: data.message || stripHtml(responseText) || `Request failed with status ${response.status}`,
                    errors: data.errors || null,
                    details: data.details || null,
                    error_code: data.error_code || `HTTP_${response.status}`,
                };
            }

            return Object.keys(data).length ? data : {
                success: false,
                message: 'The server returned an empty response.',
                error_code: 'EMPTY_RESPONSE',
            };
        })
        .then(data => {
            Swal.close();

            if (!data) return; // Skip if redirect was handled above

            if (data.success) {
                fireStyled({
                    title: 'Check Your Phone',
                    text: data.message || 'A mobile money payment request has been sent to your phone. Enter your PIN and confirm the payment to complete your order.',
                    icon: 'success',
                    showCancelButton: true,
                    reverseButtons: true,
                    confirmButtonText: '<i class="bi bi-receipt me-1"></i>My Orders',
                    cancelButtonText: '<i class="bi bi-bag me-1"></i>Continue Shopping',
                    customClass: {
                        confirmButton: 'btn btn-primary me-2',
                        cancelButton: 'btn btn-outline-secondary',
                    },
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = window.checkoutData.routes.orders;
                        return;
                    }
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = window.checkoutData.routes.shop;
                    }
                });
            } else {
                showCheckoutError(data);
                setCheckoutButtonLoading(confirmBtn, false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.close();
            showCheckoutError({
                message: error?.message || 'Could not connect to the server. Please check your connection and try again.',
                error_code: 'NETWORK_ERROR',
            }, 'Connection Error');
            setCheckoutButtonLoading(confirmBtn, false);
        });
    };

    document.querySelectorAll('.checkout-dot-btn[data-spin-link="1"]').forEach((link) => {
        link.addEventListener('click', function() {
            this.classList.add('loading');
            const text = this.querySelector('.button-text');
            if (text) text.style.opacity = '0.92';
        });
    });

    const orderSummaryToggleBtn = document.getElementById('orderSummaryToggleBtn');
    const orderSummaryExtra = document.getElementById('orderSummaryExtra');
    if (orderSummaryToggleBtn && orderSummaryExtra) {
        orderSummaryExtra.style.maxHeight = '0px';

        orderSummaryToggleBtn.addEventListener('click', function() {
            const isExpanded = this.dataset.expanded === 'true';
            const hiddenCount = this.dataset.hiddenCount || 0;

            if (isExpanded) {
                orderSummaryExtra.style.maxHeight = `${orderSummaryExtra.scrollHeight}px`;
                requestAnimationFrame(() => {
                    orderSummaryExtra.style.maxHeight = '0px';
                    orderSummaryExtra.classList.remove('is-expanded');
                });
                this.dataset.expanded = 'false';
                this.classList.remove('is-expanded');
                this.innerHTML = `<i class="bi bi-chevron-down" aria-hidden="true"></i><span>Show more (${hiddenCount})</span>`;
            } else {
                orderSummaryExtra.classList.add('is-expanded');
                orderSummaryExtra.style.maxHeight = `${orderSummaryExtra.scrollHeight}px`;
                this.dataset.expanded = 'true';
                this.classList.add('is-expanded');
                this.innerHTML = '<i class="bi bi-chevron-down" aria-hidden="true"></i><span>Show less</span>';
            }
        });
    }

    console.log('Checkout JavaScript initialized successfully');
});
    function setCheckoutButtonLoading(button, isLoading) {
        if (!button) return;
        const spinner = button.querySelector('.btn-dot-spinner');
        const text = button.querySelector('.button-text');

        if (isLoading) {
            button.classList.add('loading');
            button.disabled = true;
            if (spinner) spinner.classList.remove('d-none');
            if (text) text.style.display = 'none';
        } else {
            button.classList.remove('loading');
            button.disabled = false;
            if (spinner) spinner.classList.add('d-none');
            if (text) text.style.display = '';
        }
    }
