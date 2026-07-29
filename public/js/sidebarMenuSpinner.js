(() => {
    function isModifiedClick(event) {
        return event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0;
    }

    function shouldHandle(link) {
        if (!link || !(link instanceof HTMLAnchorElement)) return false;
        const href = link.getAttribute('href') || '';
        if (!href || href === '#' || href.startsWith('javascript:')) return false;
        if (link.target && link.target !== '_self') return false;
        if (link.classList.contains('active')) return false;
        return true;
    }

    function setLoading(link) {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        sidebar.querySelectorAll('.nav-link.sidebar-loading').forEach((el) => {
            el.classList.remove('sidebar-loading');
            el.removeAttribute('aria-busy');
        });

        if (!link.querySelector('.sidebar-link-spinner')) {
            const spinner = document.createElement('span');
            spinner.className = 'sidebar-link-spinner';
            spinner.setAttribute('aria-hidden', 'true');
            spinner.innerHTML = '<span></span><span></span><span></span>';
            link.appendChild(spinner);
        }

        link.classList.add('sidebar-loading');
        link.setAttribute('aria-busy', 'true');
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('#sidebar .nav-link');
        if (!shouldHandle(link) || isModifiedClick(event)) return;
        setLoading(link);
    });
})();
