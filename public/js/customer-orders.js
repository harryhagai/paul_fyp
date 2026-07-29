(() => {
    const page = document.querySelector('.customer-orders-page');
    if (!page) return;

    const form = document.getElementById('ordersFilterForm');
    const searchInput = document.getElementById('ordersSearch');
    const tableBody = document.getElementById('ordersTableBody');
    const loader = document.getElementById('ordersLoader');
    const sentinel = document.getElementById('ordersSentinel');
    const meta = document.getElementById('ordersMeta');
    const resetBtn = document.getElementById('ordersResetBtn');
    const cancelModalEl = document.getElementById('cancelOrderModal');
    const baseUrl = page.dataset.ordersUrl;

    let nextPageUrl = sentinel?.dataset.nextPageUrl || '';
    let loading = false;
    let filterTimer = null;
    let controller = null;
    let loadedFrom = parseInt(meta?.dataset.loadedFrom || '0', 10) || null;
    let loadedTo = parseInt(meta?.dataset.loadedTo || '0', 10) || null;

    function setOrdersLoadingState(active) {
        loading = active;
        loader?.classList.toggle('d-none', !active);
    }

    function buildOrdersUrl(pageUrl = null) {
        if (pageUrl) return pageUrl;
        const params = new URLSearchParams(new FormData(form));
        [...params.entries()].forEach(([key, value]) => {
            if (!value) params.delete(key);
        });
        const query = params.toString();
        return query ? `${baseUrl}?${query}` : baseUrl;
    }

    function updateOrdersMeta(data, append = false) {
        if (!meta) return;
        if (append) {
            loadedFrom = loadedFrom || data.from || 0;
            loadedTo = data.to || loadedTo || 0;
        } else {
            loadedFrom = data.from || 0;
            loadedTo = data.to || 0;
        }

        if (meta) {
            meta.dataset.loadedFrom = loadedFrom;
            meta.dataset.loadedTo = loadedTo;
        }

        const from = loadedFrom || 0;
        const to = loadedTo || 0;
        meta.textContent = `Showing ${from}-${to} of ${data.total} orders`;
    }

    function fetchOrdersData({ append = false, pageUrl = null } = {}) {
        if (loading) return;
        if (controller && !append) controller.abort();

        controller = new AbortController();
        setOrdersLoadingState(true);

        fetch(buildOrdersUrl(pageUrl), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
            .then(response => response.json())
            .then(data => {
                if (append) {
                    const emptyRow = tableBody.querySelector('.orders-empty-row');
                    if (emptyRow) emptyRow.remove();
                    tableBody.insertAdjacentHTML('beforeend', data.html);
                } else {
                    tableBody.innerHTML = data.html;
                    const cleanUrl = buildOrdersUrl();
                    window.history.replaceState({}, '', cleanUrl);
                }

                nextPageUrl = data.next_page_url || '';
                if (sentinel) sentinel.dataset.nextPageUrl = nextPageUrl;
                updateOrdersMeta(data, append);

                if (append && nextPageUrl) {
                    window.setTimeout(loadNextOrdersPageIfVisible, 80);
                }
            })
            .catch(error => {
                if (error.name === 'AbortError') return;
            })
            .finally(() => setOrdersLoadingState(false));
    }

    function loadNextOrdersPageIfVisible() {
        if (!sentinel || !nextPageUrl || loading) return;
        const rect = sentinel.getBoundingClientRect();
        const threshold = window.innerHeight + 180;
        if (rect.top <= threshold) {
            fetchOrdersData({ append: true, pageUrl: nextPageUrl });
        }
    }

    function queueOrdersFilter() {
        clearTimeout(filterTimer);
        filterTimer = setTimeout(() => fetchOrdersData(), 350);
    }

    form?.addEventListener('input', (event) => {
        if (event.target.matches('input[type="search"], input[type="date"]')) {
            queueOrdersFilter();
        }
    });

    form?.addEventListener('change', (event) => {
        if (event.target.matches('select, input[type="date"]')) {
            queueOrdersFilter();
        }
    });

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        fetchOrdersData();
    });

    searchInput?.addEventListener('keyup', () => {
        queueOrdersFilter();
    });

    searchInput?.addEventListener('search', () => {
        queueOrdersFilter();
    });

    resetBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        form.querySelectorAll('input, select').forEach((field) => {
            field.value = '';
        });
        fetchOrdersData();
    });

    document.addEventListener('click', (event) => {
        const cancelBtn = event.target.closest('.cancel-order-btn');
        if (cancelBtn) {
            document.getElementById('cancelOrderNumber').textContent = cancelBtn.dataset.orderNumber;
            document.getElementById('cancelOrderForm').action = `/customer/orders/${cancelBtn.dataset.orderId}/cancel`;
            new bootstrap.Modal(cancelModalEl).show();
            return;
        }

        const viewBtn = event.target.closest('.view-order-btn');
        if (viewBtn) setButtonLoadingState(viewBtn);
    });

    document.getElementById('cancelOrderForm')?.addEventListener('submit', function() {
        setButtonLoadingState(this.querySelector('button[type="submit"]'));
    });

    if (sentinel && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && nextPageUrl && !loading) {
                fetchOrdersData({ append: true, pageUrl: nextPageUrl });
            }
        }, { rootMargin: '180px' });

        observer.observe(sentinel);
    }

    function setButtonLoadingState(button) {
        const spinner = button?.querySelector('.spinner-border, .btn-dot-spinner');
        const text = button?.querySelector('.btn-text');
        if (spinner) spinner.classList.remove('d-none');
        if (text) text.classList.add('d-none');
        if (button) button.disabled = true;
    }

    function clearButtonLoadingState(button) {
        const spinner = button?.querySelector('.spinner-border, .btn-dot-spinner');
        const text = button?.querySelector('.btn-text');
        if (spinner) spinner.classList.add('d-none');
        if (text) text.classList.remove('d-none');
        if (button) button.disabled = false;
    }
})();
