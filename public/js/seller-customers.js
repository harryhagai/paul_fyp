$(document).ready(function () {
    const page = $('#sellerCustomersPage');
    const storeUrl = page.data('store-url');
    const showUrlTemplate = page.data('show-url');
    const updateUrlTemplate = page.data('update-url');
    const deleteUrlTemplate = page.data('delete-url');
    const csrfToken = page.data('csrf-token');
    const avatarBaseUrl = page.data('avatar-base-url');

    function showValidationErrors(xhr, fallback) {
        const errors = xhr.responseJSON?.errors;
        const message = xhr.responseJSON?.message || fallback;

        if (errors) {
            let errorMessage = '<ul>';
            $.each(errors, function (key, value) {
                errorMessage += '<li>' + value[0] + '</li>';
            });
            errorMessage += '</ul>';

            Swal.fire({ icon: 'error', title: 'Validation Error', html: errorMessage, confirmButtonText: 'OK' });
            return;
        }

        Swal.fire({ icon: 'error', title: 'Error', text: message, confirmButtonText: 'OK' });
    }

    $('#addCustomerForm').submit(function (e) {
        e.preventDefault();
        const submitBtn = $('#saveCustomerBtn');
        const spinner = submitBtn.find('.spinner-border');
        submitBtn.prop('disabled', true);
        spinner.show();

        $.ajax({
            url: storeUrl,
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            success: function (response) {
                if (!response.success) return;
                Swal.fire({ icon: 'success', title: 'Success!', text: response.message, showConfirmButton: false, timer: 2000, timerProgressBar: true });
                $('#addCustomerForm')[0].reset();
                $('#addCustomerModal').modal('hide');
                setTimeout(function () { location.reload(); }, 2000);
            },
            error: function (xhr) { showValidationErrors(xhr, 'An error occurred'); },
            complete: function () { submitBtn.prop('disabled', false); spinner.hide(); }
        });
    });

    $('.edit-btn').click(function () {
        const customerId = $(this).data('customer-id');
        $.ajax({
            url: showUrlTemplate.replace(':id', customerId),
            type: 'GET',
            success: function (response) {
                if (!response.success) return;
                const customer = response.customer;
                $('#editCustomerId').val(customer.public_id || customer.id);
                $('#editName').val(customer.name);
                $('#editEmail').val(customer.email);
                if ($('#editRole').length) $('#editRole').val(customer.role);
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to load customer data', confirmButtonText: 'OK' });
            }
        });
    });

    $('#editCustomerForm').submit(function (e) {
        e.preventDefault();
        const submitBtn = $('#updateCustomerBtn');
        const spinner = submitBtn.find('.spinner-border');
        submitBtn.prop('disabled', true);
        spinner.show();

        $.ajax({
            url: updateUrlTemplate.replace(':id', $('#editCustomerId').val()),
            type: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'X-HTTP-Method-Override': 'PUT' },
            success: function (response) {
                if (!response.success) return;
                Swal.fire({ icon: 'success', title: 'Success!', text: response.message, showConfirmButton: false, timer: 2000, timerProgressBar: true });
                $('#editCustomerModal').modal('hide');
                setTimeout(function () { location.reload(); }, 2000);
            },
            error: function (xhr) { showValidationErrors(xhr, 'An error occurred'); },
            complete: function () { submitBtn.prop('disabled', false); spinner.hide(); }
        });
    });

    $('.delete-btn').click(function () {
        const customerId = $(this).data('customer-id');
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
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: deleteUrlTemplate.replace(':id', customerId),
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken },
                success: function (response) {
                    if (!response.success) return;
                    Swal.fire({ icon: 'success', title: 'Deleted!', text: response.message, showConfirmButton: false, timer: 1500 });
                    setTimeout(function () { location.reload(); }, 1500);
                },
                error: function (xhr) {
                    Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to delete customer', confirmButtonText: 'OK' });
                }
            });
        });
    });

    $('.view-btn').click(function () {
        const customerId = $(this).data('customer-id');
        $.ajax({
            url: showUrlTemplate.replace(':id', customerId),
            type: 'GET',
            success: function (response) {
                if (!response.success) return;
                const customer = response.customer;
                $('#viewName').text(customer.name);
                $('#viewEmail').text(customer.email);
                $('#viewJoinDate').text(new Date(customer.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }));
                $('#viewRole').text(customer.role).removeClass('bg-success bg-primary bg-warning text-dark bg-secondary');
                if (customer.role === 'admin') $('#viewRole').addClass('bg-primary');
                else if (customer.role === 'seller') $('#viewRole').addClass('bg-warning text-dark');
                else $('#viewRole').addClass('bg-success');

                const avatarContainer = $('#viewAvatarContainer');
                avatarContainer.empty();
                if (customer.profile_photo) {
                    avatarContainer.append($('<img>', { src: avatarBaseUrl + '/' + customer.profile_photo, alt: 'Profile Picture', class: 'rounded-circle', css: { width: '80px', height: '80px', 'object-fit': 'cover' } }));
                } else {
                    const avatarDiv = $('<div>', { class: 'avatar-large rounded-circle d-flex align-items-center justify-content-center', css: { width: '80px', height: '80px', background: 'linear-gradient(135deg, var(--teal-primary, #0d9488) 0%, var(--teal-secondary, #0f766e) 100%)', color: 'white', 'font-weight': 'bold', 'font-size': '24px' } });
                    avatarDiv.append($('<span>').text(customer.name.charAt(0).toUpperCase()));
                    avatarContainer.append(avatarDiv);
                }
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to load customer data', confirmButtonText: 'OK' });
            }
        });
    });

    $('.toggle-password').click(function () {
        const input = $(this).siblings('input');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    $('#refreshBtn').click(function () { location.reload(); });

    let searchDebounceTimer = null;
    const searchInput = $('#searchInput');
    const searchForm = $('#searchForm');
    const searchSpinner = $('#searchSpinner');
    const searchIcon = $('#searchIcon');

    function setSearchLoading(isLoading) {
        if (isLoading) {
            searchSpinner.removeClass('d-none');
            searchIcon.addClass('d-none');
        } else {
            searchSpinner.addClass('d-none');
            searchIcon.removeClass('d-none');
        }
    }

    function triggerAutoSearch() {
        setSearchLoading(true);
        searchForm.trigger('submit');
    }

    searchInput.on('input', function () {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(triggerAutoSearch, 500);
    });

    searchInput.on('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(searchDebounceTimer);
            triggerAutoSearch();
        }
    });

    $('.modal').on('hidden.bs.modal', function () {
        const form = $(this).find('form')[0];
        if (form) form.reset();
    });

    $('.modal').on('show.bs.modal', function () { $(this).addClass('zoom-modal'); });

    $('[data-bs-toggle="modal"]').css({ transition: 'none', transform: 'none' });
});
