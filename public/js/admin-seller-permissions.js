document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.permission-group-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            const group = this.getAttribute('data-group');
            const mode = this.getAttribute('data-mode');
            const toggles = document.querySelectorAll('.permission-toggle[data-group="' + group + '"]');

            toggles.forEach(function (toggle) {
                toggle.checked = mode === 'all';
            });
        });
    });
});
