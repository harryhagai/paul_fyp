(function () {
    const page = document.getElementById('sellerNotificationsPage');
    if (!page) return;

    const csrf = page.dataset.csrf;
    const baseUrl = page.dataset.baseUrl;
    const markAllUrl = page.dataset.markAllUrl;
    const detailPanel = document.getElementById('notificationDetailPanel');

    function req(url, method) {
        return fetch(url, {
            method,
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Content-Type': 'application/json'
            }
        }).then((r) => r.json().catch(() => ({})));
    }

    function reloadSoon(ms) {
        setTimeout(function () { window.location.reload(); }, ms || 600);
    }

    function renderDetail(data) {
        if (!detailPanel) return;
        const isRead = data.read === '1';
        const priority = (data.priority || 'low').toLowerCase();

        const actionBtn = data.actionUrl
            ? `<a href="${data.actionUrl}" class="btn btn-sm btn-outline-primary themed-outline-btn"><i class="bi bi-link-45deg me-1"></i>Action</a>`
            : '';

        const markReadBtn = !isRead
            ? `<button class="btn btn-sm btn-outline-primary themed-outline-btn" id="detailMarkReadBtn" data-id="${data.id}"><i class="bi bi-check2 me-1"></i>Mark Read</button>`
            : '';

        const expires = data.expires
            ? `<small class="text-muted d-block"><i class="bi bi-calendar-x me-1"></i>Expires ${data.expires}</small>`
            : '';

        detailPanel.innerHTML = `
            <div class="notification-detail-wrap" data-current-id="${data.id}">
                <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                    <h5 class="notification-title mb-0">${data.title}</h5>
                    ${!isRead ? '<span class="badge notification-badge-new">New</span>' : ''}
                    <span class="badge notification-badge-priority priority-${priority}">${priority.charAt(0).toUpperCase() + priority.slice(1)}</span>
                </div>
                <div class="notification-detail-meta mb-3">
                    <small class="text-muted d-block"><i class="bi bi-clock me-1"></i>${data.created}</small>
                    ${expires}
                </div>
                <div class="notification-detail-message mb-3">${data.message}</div>
                <div class="d-flex flex-wrap gap-2" id="notificationDetailActions">
                    ${actionBtn}
                    ${markReadBtn}
                    <button class="btn btn-sm btn-outline-danger" id="detailDeleteBtn" data-id="${data.id}"><i class="bi bi-trash me-1"></i>Delete</button>
                </div>
            </div>
        `;
    }

    function currentSelectionData(item) {
        return {
            id: item.dataset.id,
            title: item.dataset.title || '',
            message: item.dataset.message || '',
            priority: item.dataset.priority || 'low',
            created: item.dataset.created || '',
            expires: item.dataset.expires || '',
            actionUrl: item.dataset.actionUrl || '',
            read: item.dataset.read || '1'
        };
    }

    document.querySelectorAll('.notification-list-item').forEach((item) => {
        item.addEventListener('click', function () {
            document.querySelectorAll('.notification-list-item').forEach((x) => x.classList.remove('active'));
            this.classList.add('active');
            renderDetail(currentSelectionData(this));
        });
    });

    const markAllBtn = document.getElementById('markAllBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            req(markAllUrl, 'PATCH').then((data) => { if (data.success) reloadSoon(500); });
        });
    }

    document.addEventListener('click', function (event) {
        const markReadBtn = event.target.closest('#detailMarkReadBtn');
        if (markReadBtn) {
            const id = markReadBtn.dataset.id;
            req(baseUrl + '/' + id + '/read', 'PATCH').then((data) => { if (data.success) reloadSoon(500); });
            return;
        }

        const deleteBtn = event.target.closest('#detailDeleteBtn');
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
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
                req(baseUrl + '/' + id, 'DELETE').then(() => reloadSoon(400));
            });
        }
    });
})();
