document.addEventListener('DOMContentLoaded', function () {
    var footer = document.querySelector('.footer');
    if (!footer) return;

    function setFooterOffset() {
        document.documentElement.style.setProperty(
            '--footer-fixed-height',
            Math.ceil(footer.getBoundingClientRect().height) + 'px'
        );
    }

    setFooterOffset();
    window.addEventListener('resize', setFooterOffset);

    if ('ResizeObserver' in window) {
        new ResizeObserver(setFooterOffset).observe(footer);
    }
});
