document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarToggleIcon = document.getElementById('sidebarToggleIcon');
    const sidebar = document.getElementById('sidebar');
    const body = document.body;

    if (!sidebarToggle || !sidebar) return;

    function isMobile() {
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function updateIcon() {
        if (!sidebarToggleIcon) return;

        const collapsedDesktop = body.classList.contains('sidebar-collapsed');
        const mobileOpen = sidebar.classList.contains('active');

        if ((isMobile() && mobileOpen) || (!isMobile() && !collapsedDesktop)) {
            sidebarToggleIcon.classList.remove('bi-layout-sidebar-inset');
            sidebarToggleIcon.classList.add('bi-layout-sidebar');
        } else {
            sidebarToggleIcon.classList.remove('bi-layout-sidebar');
            sidebarToggleIcon.classList.add('bi-layout-sidebar-inset');
        }
    }

    function applySavedState() {
        const desktopCollapsed = localStorage.getItem('sellerSidebarCollapsedDesktop') === 'true';
        if (!isMobile() && desktopCollapsed) {
            body.classList.add('sidebar-collapsed');
            sidebar.classList.add('collapsed');
        } else {
            body.classList.remove('sidebar-collapsed');
            sidebar.classList.remove('collapsed');
        }

        if (isMobile()) {
            sidebar.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
            const mobileOpen = localStorage.getItem('sellerSidebarOpenMobile') === 'true';
            if (mobileOpen) {
                sidebar.classList.add('active');
            } else {
                sidebar.classList.remove('active');
            }
        } else {
            sidebar.classList.remove('active');
            localStorage.setItem('sellerSidebarOpenMobile', 'false');
        }

        updateIcon();
    }

    sidebarToggle.addEventListener('click', function () {
        if (isMobile()) {
            sidebar.classList.toggle('active');
            localStorage.setItem('sellerSidebarOpenMobile', sidebar.classList.contains('active') ? 'true' : 'false');
        } else {
            body.classList.toggle('sidebar-collapsed');
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('sellerSidebarCollapsedDesktop', body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
        }
        updateIcon();
    });

    sidebar.querySelectorAll('.nav-link').forEach((link) => {
        link.addEventListener('click', function () {
            if (!isMobile()) return;
            sidebar.classList.remove('active');
            localStorage.setItem('sellerSidebarOpenMobile', 'false');
            updateIcon();
        });
    });

    document.addEventListener('click', function (event) {
        if (!isMobile()) return;
        if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
            sidebar.classList.remove('active');
            localStorage.setItem('sellerSidebarOpenMobile', 'false');
            updateIcon();
        }
    });

    window.addEventListener('resize', applySavedState);

    applySavedState();
});
