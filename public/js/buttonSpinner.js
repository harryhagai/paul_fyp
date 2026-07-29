(() => {
    const SPINNER_CLASS = 'spinner-border spinner-border-sm me-2';

    function createSpinner() {
        const spinner = document.createElement('span');
        spinner.className = SPINNER_CLASS;
        spinner.setAttribute('aria-hidden', 'true');
        return spinner;
    }

    function shouldSkip(button) {
        return !button || button.dataset.noSpinner !== undefined || button.disabled;
    }

    function applyLoadingState(button) {
        if (shouldSkip(button) || button.dataset.spinnerActive === '1') {
            return;
        }

        button.dataset.spinnerActive = '1';
        button.disabled = true;

        if (button instanceof HTMLInputElement && button.type === 'submit') {
            button.dataset.originalValue = button.value;
            button.value = button.dataset.loadingText || 'Please wait...';
            return;
        }

        if (button instanceof HTMLButtonElement) {
            button.dataset.originalHtml = button.innerHTML;
            button.innerHTML = '';
            button.appendChild(createSpinner());

            const loadingText = button.dataset.loadingText || 'Please wait...';
            const textNode = document.createElement('span');
            textNode.textContent = loadingText;
            button.appendChild(textNode);
        }
    }

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        const submitter = event.submitter || form.querySelector('button[type="submit"], input[type="submit"]');
        if (!(submitter instanceof HTMLButtonElement) && !(submitter instanceof HTMLInputElement)) {
            return;
        }

        applyLoadingState(submitter);
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-spin-on-click]');
        if (!button || !(button instanceof HTMLButtonElement)) {
            return;
        }

        applyLoadingState(button);
    });
})();
