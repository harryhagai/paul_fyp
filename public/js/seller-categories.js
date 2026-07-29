(function () {
    const pageEl = document.querySelector('.seller-categories-page');
    if (!pageEl || !window.jQuery) return;

    const $ = window.jQuery;
    const config = {
        storeUrl: pageEl.dataset.storeUrl,
        showTpl: pageEl.dataset.showUrlTemplate,
        updateTpl: pageEl.dataset.updateUrlTemplate,
        destroyTpl: pageEl.dataset.destroyUrlTemplate,
        nextPageUrl: pageEl.dataset.nextPageUrl || null,
        csrf: pageEl.dataset.csrf
    };

    let loadingCategories = false;
    let nextPageUrl = config.nextPageUrl;

    function tpl(url, id) {
        return url.replace('__ID__', id);
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': config.csrf
        }
    });

    function loadMoreCategories() {
        if (loadingCategories || !nextPageUrl) return;

        loadingCategories = true;
        $('#lazyLoader').removeClass('d-none');

        $.ajax({
            url: nextPageUrl,
            method: 'GET',
            dataType: 'json'
        }).done(function (response) {
            if (response.summary_html) {
                $('#categorySummaryList').append(response.summary_html);
            }

            if (response.table_html) {
                $('#categoriesTableBody').append(response.table_html);
            }

            nextPageUrl = response.next_page_url || null;
            pageEl.dataset.nextPageUrl = nextPageUrl || '';
        }).fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load more categories'
            });
        }).always(function () {
            loadingCategories = false;
            $('#lazyLoader').addClass('d-none');
        });
    }

    const sentinel = document.getElementById('scrollSentinel');
    if (sentinel && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) {
                loadMoreCategories();
            }
        }, { rootMargin: '300px 0px' });

        observer.observe(sentinel);
    }

    $('#createCategoryForm').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const $spinner = $submitBtn.find('.spinner-border');

        $submitBtn.prop('disabled', true);
        $spinner.removeClass('d-none');

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').hide();

        $.ajax({
            url: config.storeUrl,
            method: 'POST',
            data: $form.serialize()
        }).done(function (response) {
            if (!response.success) return;
            $('#createCategoryModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            }).then(function () {
                window.location.reload();
            });
        }).fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                Object.keys(errors).forEach(function (key) {
                    const $field = $('#create_' + key);
                    $field.addClass('is-invalid');
                    $field.next('.invalid-feedback').text(errors[key][0]).show();
                });
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'An error occurred'
            });
        }).always(function () {
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
        });
    });

    window.editCategory = function (id) {
        $.ajax({
            url: tpl(config.showTpl, id),
            method: 'GET'
        }).done(function (response) {
            if (!response.success) return;

            const category = response.category;
            $('#edit_category_id').val(category.public_id || category.id);
            $('#edit_name').val(category.name);
            $('#edit_description').val(category.description || '');
            $('#editCategoryModal').modal('show');
        }).fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load category details'
            });
        });
    };

    $('#editCategoryForm').on('submit', function (e) {
        e.preventDefault();

        const categoryId = $('#edit_category_id').val();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const $spinner = $submitBtn.find('.spinner-border');

        $submitBtn.prop('disabled', true);
        $spinner.removeClass('d-none');

        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').hide();

        $.ajax({
            url: tpl(config.updateTpl, categoryId),
            method: 'PUT',
            data: $form.serialize()
        }).done(function (response) {
            if (!response.success) return;
            $('#editCategoryModal').modal('hide');
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.message,
                timer: 2000,
                showConfirmButton: false
            }).then(function () {
                window.location.reload();
            });
        }).fail(function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON?.errors || {};
                Object.keys(errors).forEach(function (key) {
                    const $field = $('#edit_' + key);
                    $field.addClass('is-invalid');
                    $field.next('.invalid-feedback').text(errors[key][0]).show();
                });
                return;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'An error occurred'
            });
        }).always(function () {
            $submitBtn.prop('disabled', false);
            $spinner.addClass('d-none');
        });
    });

    window.deleteCategory = function (id, name) {
        Swal.fire({
            title: 'Delete item?',
            text: 'This action cannot be undone.',
            icon: 'warning',
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
        }).then(function (result) {
            if (!result.isConfirmed) return;

            $.ajax({
                url: tpl(config.destroyTpl, id),
                method: 'DELETE'
            }).done(function (response) {
                if (!response.success) return;
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(function () {
                    window.location.reload();
                });
            }).fail(function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to delete category'
                });
            });
        });
    };

    window.showCategory = function (id) {
        $.ajax({
            url: tpl(config.showTpl, id),
            method: 'GET'
        }).done(function (response) {
            if (!response.success) return;

            const category = response.category;
            $('#view_name').val(category.name || '');
            $('#view_description').val(category.description || 'No description');
            $('#view_products_count').val(category.products_count ?? 0);
            $('#view_created_at').val(category.created_at ? new Date(category.created_at).toLocaleDateString() : '');
            $('#view_slug').val(category.slug || '');
            $('#viewCategoryModal').modal('show');
        }).fail(function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load category details'
            });
        });
    };

    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0]?.reset();
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback').hide();
    });
})();
