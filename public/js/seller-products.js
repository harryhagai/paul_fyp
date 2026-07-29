(function () {
    const pageEl = document.querySelector('.seller-products-page');
    if (!pageEl || !window.jQuery) return;
    const $ = window.jQuery;

    const config = {
        indexUrl: pageEl.dataset.indexUrl,
        storeUrl: pageEl.dataset.storeUrl,
        showTpl: pageEl.dataset.showUrlTemplate,
        updateTpl: pageEl.dataset.updateUrlTemplate,
        destroyTpl: pageEl.dataset.destroyUrlTemplate,
        toggleTpl: pageEl.dataset.toggleAdvertisedUrlTemplate,
        csrf: pageEl.dataset.csrf
    };
    const rootStyle = getComputedStyle(document.documentElement);
    const primaryColor = rootStyle.getPropertyValue('--teal-primary').trim() || '#0d9488';

    let nextPageUrl = null;
    let loadingList = false;
    let searchTimer = null;
    let autoRefreshInterval = null;
    let isModalOpen = false;

    function tpl(url, id) { return url.replace('__ID__', id); }
    function formQuery() { return $('#searchForm').serialize(); }
    function getSearchBtnState(loading) {
        $('#searchSpinner').toggleClass('d-none', !loading);
        $('#searchIcon').toggleClass('d-none', loading);
        $('#searchBtn').prop('disabled', loading);
    }
    function showLoading() { $('#loadingOverlay').css('display', 'flex'); }
    function hideLoading() { $('#loadingOverlay').hide(); }

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': config.csrf } });

    function loadProducts(url, append) {
        if (loadingList || !url) return;
        loadingList = true;
        $('#lazyLoader').removeClass('d-none');
        getSearchBtnState(true);
        $.get(url, function (res) {
            if (!append) $('#productsTableBody').html(res.html);
            else $('#productsTableBody').append(res.html);
            nextPageUrl = res.next_page_url;
        }).always(function () {
            loadingList = false;
            $('#lazyLoader').addClass('d-none');
            getSearchBtnState(false);
        });
    }

    function runSearch() {
        const url = `${config.indexUrl}?${formQuery()}`;
        loadProducts(url, false);
    }

    $('#searchForm').on('submit', function (e) { e.preventDefault(); runSearch(); });
    $('#searchInput, select[name="category_id"]').on('input change', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(runSearch, 350);
    });

    $('#refreshBtn').on('click', function (e) { e.preventDefault(); runSearch(); });

    const sentinel = document.getElementById('scrollSentinel');
    if (sentinel && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && nextPageUrl) {
                loadProducts(nextPageUrl, true);
            }
        }, { rootMargin: '300px 0px' });
        io.observe(sentinel);
    }

    // initialize from server-rendered pagination
    const nextLink = document.querySelector('#productsPagination .pagination .page-item.active + .page-item a');
    nextPageUrl = nextLink ? nextLink.getAttribute('href') : null;

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

    $('#autoRefresh').on('change', function () {
        if (this.checked) startAutoRefresh();
        else stopAutoRefresh();
    });

    $('.modal').on('show.bs.modal', function () { isModalOpen = true; });
    $('.modal').on('hidden.bs.modal', function () { isModalOpen = false; });

    $(window).on('beforeunload', function () { stopAutoRefresh(); });

    $('#addProductForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#saveProductBtn');
        const $sp = $btn.find('.spinner-border');
        $btn.prop('disabled', true); $sp.show(); showLoading();
        $.ajax({
            url: config.storeUrl, method: 'POST',
            data: new FormData(this), processData: false, contentType: false
        }).done(function (r) {
            if (!r.success) return;
            Swal.fire({ icon: 'success', title: 'Success', text: r.message, timer: 1400, showConfirmButton: false });
            $('#addProductModal').modal('hide');
            $('#addProductForm')[0].reset();
            runSearch();
        }).fail(function (xhr) {
            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Request failed' });
        }).always(function () { $btn.prop('disabled', false); $sp.hide(); hideLoading(); });
    });

    $(document).on('click', '.edit-btn', function () {
        const id = $(this).data('product-id');
        showLoading();
        $.get(tpl(config.showTpl, id), function (res) {
            const p = res.product;
            $('#editProductId').val(p.public_id || p.id);
            $('#editName').val(p.name);
            $('#editCategory').val(p.category_id);
            $('#editNewPrice').val(p.new_price);
            $('#editOldPrice').val(p.old_price || '');
            $('#editStock').val(p.stock);
            $('#editDescription').val(p.description?.description || '');
            $('#editSpecifications').val(p.description?.specifications || '');
            $('#editDetails').val(p.description?.details || '');
        }).always(hideLoading);
    });

    $('#editProductForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#editProductId').val();
        const $btn = $('#updateProductBtn');
        const $sp = $btn.find('.spinner-border');
        $btn.prop('disabled', true); $sp.show(); showLoading();
        $.ajax({
            url: tpl(config.updateTpl, id), method: 'POST',
            data: new FormData(this), processData: false, contentType: false,
            headers: { 'X-HTTP-Method-Override': 'PUT' }
        }).done(function (r) {
            if (!r.success) return;
            Swal.fire({ icon: 'success', title: 'Updated', text: r.message, timer: 1400, showConfirmButton: false });
            $('#editProductModal').modal('hide');
            runSearch();
        }).fail(function (xhr) {
            Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Request failed' });
        }).always(function () { $btn.prop('disabled', false); $sp.hide(); hideLoading(); });
    });

    $(document).on('click', '.delete-btn', function () {
        const id = $(this).data('product-id');
        Swal.fire({
            icon: 'warning',
            title: 'Delete item?',
            text: 'This action cannot be undone.',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-trash me-1"></i>Yes, delete it!',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Cancel',
            buttonsStyling: false,
            customClass: {
                popup: 'category-delete-popup',
                icon: 'category-delete-icon',
                title: 'category-delete-title',
                htmlContainer: 'category-delete-text',
                actions: 'category-delete-actions',
                confirmButton: 'category-delete-confirm btn',
                cancelButton: 'category-delete-cancel btn'
            }
        }).then((r) => {
            if (!r.isConfirmed) return;
            $.ajax({ url: tpl(config.destroyTpl, id), method: 'DELETE' })
                .done(() => runSearch())
                .fail((xhr) => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Delete failed' }));
        });
    });

    $(document).on('click', '.view-btn, .thumbnail-clickable', function () {
        const id = $(this).data('product-id');
        $.get(tpl(config.showTpl, id), function (res) {
            const p = res.product;
            $('#viewName').text(p.name);
            $('#viewCategory').text(p.category?.name || 'N/A');
            $('#viewThumbnail').attr('src', p.thumbnail ? `/storage/${p.thumbnail}` : 'https://via.placeholder.com/200?text=No+Image');
            $('#viewPrice').text(`Tsh${Number(p.new_price).toFixed(2)}`);
            $('#viewStock').text(`Stock: ${p.stock}`);
            $('#viewDescription').text(p.description?.description || 'No description available');
            $('#viewSpecifications').text(p.description?.specifications || 'No specifications available');
            $('#viewDetails').text(p.description?.details || 'No details available');
            $('#viewStatus').text(p.is_advertised ? 'Advertised' : 'Normal');
            $('#viewDiscount').text(p.discount > 0 ? `${p.discount}% OFF` : 'No Discount');
        });
    });

    $(document).on('change', '.advertised-toggle', function () {
        const id = $(this).data('product-id');
        $.ajax({ url: tpl(config.toggleTpl, id), method: 'PATCH' })
            .fail(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to update advertise status' }));
    });

    $(document).on('change', '.rating-select', function () {
        const id = $(this).data('product-id');
        $.post(tpl(config.updateTpl, id), { _method: 'PUT', rate: $(this).val() });
    });
})();
