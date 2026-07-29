(function () {
    const pageEl = document.querySelector('.seller-orders-page');
    if (!pageEl || !window.jQuery) return;

    const $ = window.jQuery;
    const themePrimary = getComputedStyle(document.documentElement)
        .getPropertyValue('--teal-primary')
        .trim() || '#0d9488';
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let nextPageUrl = null;
    let loadingList = false;
    let searchTimer = null;
    let autoRefreshInterval = null;
    let isModalOpen = false;

    function buildYesActionLabel(status) {
        const normalized = String(status || '').toLowerCase();
        if (normalized === 'completed') return 'Yes Complete';
        if (normalized === 'cancelled') return 'Yes Cancel';
        return 'Yes Confirm';
    }

    function buildYesActionClass(status) {
        const normalized = String(status || '').toLowerCase();
        if (normalized === 'cancelled') return 'swal-confirm-danger';
        return 'swal-confirm-success';
    }

    function buildQuestionIconClass(status) {
        const normalized = String(status || '').toLowerCase();
        if (normalized === 'cancelled') return 'category-delete-icon swal-icon-danger';
        return 'category-delete-icon swal-icon-success';
    }

    function fireStyled(options) {
        const opts = options || {};
        const optionCustomClass = opts.customClass || {};
        const restOptions = Object.assign({}, opts);
        delete restOptions.customClass;

        const baseClass = {
            popup: 'category-delete-popup',
            icon: 'category-delete-icon',
            title: 'category-delete-title',
            htmlContainer: 'category-delete-text',
            actions: 'category-delete-actions',
            confirmButton: 'category-delete-confirm btn',
            cancelButton: 'category-delete-cancel btn'
        };

        return Swal.fire(Object.assign({
            buttonsStyling: false,
            customClass: Object.assign({}, baseClass, optionCustomClass)
        }, restOptions));
    }

    function formQuery() {
        return $('#searchForm').serialize();
    }

    function showLoading() {
        if (!$('#loadingOverlay').length) {
            $('body').append(
                '<div id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.8); display: none; justify-content: center; align-items: center; z-index: 1050;">' +
                    '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>' +
                '</div>'
            );
        }
        $('#loadingOverlay').css('display', 'flex');
    }

    function hideLoading() {
        $('#loadingOverlay').hide();
    }

    function loadOrders(url, append) {
        if (loadingList || !url) return;
        loadingList = true;
        $('#lazyLoader').removeClass('d-none');

        $.get(url, function (res) {
            if (!append) {
                $('#ordersTableBody').html(res.html);
            } else {
                $('#ordersTableBody').append(res.html);
            }
            nextPageUrl = res.next_page_url;
        }).always(function () {
            loadingList = false;
            $('#lazyLoader').addClass('d-none');
        });
    }

    function runSearch() {
        const baseUrl = $('#searchForm').attr('action');
        const url = baseUrl + '?' + formQuery();
        loadOrders(url, false);
    }

    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
        }
    }

    function startAutoRefresh() {
        stopAutoRefresh();
        autoRefreshInterval = setInterval(function () {
            if (!document.hidden && !isModalOpen && !loadingList) {
                runSearch();
            }
        }, 30000);
    }

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': csrfToken } });

    $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        runSearch();
    });

    $('#searchForm input[name="search"], #searchForm select[name="status"], #searchForm input[name="date_from"], #searchForm input[name="date_to"]').on('input change', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 350);
    });

    $('#refreshBtn').on('click', function (e) {
        e.preventDefault();
        runSearch();
    });

    $('#autoRefresh').on('change', function () {
        if (this.checked) startAutoRefresh();
        else stopAutoRefresh();
    });

    const sentinel = document.getElementById('scrollSentinel');
    if (sentinel && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting && nextPageUrl) {
                loadOrders(nextPageUrl, true);
            }
        }, { rootMargin: '300px 0px' });
        io.observe(sentinel);
    }

    const nextLink = document.querySelector('#ordersPagination .pagination .page-item.active + .page-item a');
    nextPageUrl = nextLink ? nextLink.getAttribute('href') : null;

    $(document).on('click', '.status-action-btn', function () {
        const $btn = $(this);
        const orderId = $btn.data('order-id');
        const currentStatus = String($btn.data('current-status') || '');
        const newStatus = String($btn.data('new-status') || '');
        const orderNumber = String($btn.data('order-number') || '');
        if (!newStatus || !orderId) return;

        fireStyled({
            icon: 'question',
            title: 'Confirm status change',
            text: 'Order ' + orderNumber + ': change status from ' + currentStatus + ' to ' + newStatus + '?',
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i>' + buildYesActionLabel(newStatus),
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
            confirmButtonColor: themePrimary,
            reverseButtons: false,
            customClass: {
                icon: buildQuestionIconClass(newStatus),
                confirmButton: 'category-delete-confirm btn ' + buildYesActionClass(newStatus),
                cancelButton: 'category-delete-cancel btn'
            }
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            $btn.prop('disabled', true);
            showLoading();
            $.ajax({
                url: '/seller/orders/' + orderId + '/status',
                type: 'PATCH',
                data: { status: newStatus, _token: csrfToken }
            }).done(function (response) {
                if (!response.success) return;

                fireStyled({
                    icon: 'success',
                    title: 'Updated',
                    text: response.message,
                    showCloseButton: true,
                    confirmButtonColor: themePrimary,
                    confirmButtonText: 'OK',
                    timer: 1600,
                    timerProgressBar: true
                });
                runSearch();
            }).fail(function (xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Failed to update order status';
                fireStyled({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    confirmButtonText: 'OK',
                    confirmButtonColor: themePrimary
                });
            }).always(function () {
                $btn.prop('disabled', false);
                hideLoading();
            });
        });
    });

    function loadOrderDetails(orderId) {
        showLoading();
        $.ajax({
            url: '/seller/orders/' + orderId,
            type: 'GET'
        }).done(function (data) {
            const order = data.order;
            const orderNumber = order.order_number || order.public_id || 'N/A';
            let itemsHtml = '';

            order.order_items.forEach(function (item, index) {
                itemsHtml += '<tr>' +
                    '<td>' + (index + 1) + '</td>' +
                    '<td>' + item.product.name + '</td>' +
                    '<td>' + item.quantity + '</td>' +
                    '<td>Tsh ' + parseFloat(item.price).toFixed(0) + '</td>' +
                    '<td>Tsh ' + (parseFloat(item.price) * item.quantity).toFixed(0) + '</td>' +
                '</tr>';
            });

            const content = '<div class="row g-3">' +
                '<div class="info-grid">' +
                '<div class="info-card">' +
                '<h6><i class="bi bi-receipt me-2"></i>Order Information</h6>' +
                '<div class="info-item"><strong>Order Number:</strong> <span class="font-monospace">' + orderNumber + '</span></div>' +
                '<div class="info-item"><strong>Status:</strong> <span class="order-status-badge status-' + order.status + '">' + order.status.charAt(0).toUpperCase() + order.status.slice(1) + '</span></div>' +
                '<div class="info-item"><strong>Order Date:</strong> ' + new Date(order.created_at).toLocaleString() + '</div>' +
                '</div>' +
                '<div class="info-card">' +
                '<h6><i class="bi bi-person me-2"></i>Customer</h6>' +
                '<div class="info-item"><strong>Name:</strong> ' + order.user.name + '</div>' +
                '<div class="info-item"><strong>Email:</strong> ' + order.user.email + '</div>' +
                '<div class="info-item"><strong>Phone:</strong> ' + (order.user.phone || 'N/A') + '</div>' +
                '</div>' +
                '<div class="info-card items-card">' +
                '<h6><i class="bi bi-box-seam me-2"></i>Order Items</h6>' +
                '<div class="items-table-wrap table-responsive"><table class="table table-sm mb-0"><thead><tr><th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>' +
                itemsHtml +
                '<tr class="table-active"><td colspan="4"><strong>Order Total</strong></td><td><strong>Tsh ' + parseFloat(order.total_amount).toFixed(0) + '</strong></td></tr>' +
                '</tbody></table></div>' +
                '</div>' +
                '</div>' +
                '</div>';

            $('#orderDetailsContent').html(content);
        }).fail(function () {
            $('#orderDetailsContent').html('<div class="alert alert-danger">Failed to load order details</div>');
        }).always(hideLoading);
    }

    $(document).on('click', '.view-btn', function () {
        loadOrderDetails($(this).data('order-id'));
    });

    $('.modal').on('show.bs.modal', function () { isModalOpen = true; });
    $('.modal').on('hidden.bs.modal', function () { isModalOpen = false; });

    $(window).on('beforeunload', function () { stopAutoRefresh(); });
})();
