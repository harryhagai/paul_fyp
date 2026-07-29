(() => {
    function createDotsSpinner() {
        const spinner = document.createElement("span");
        spinner.className = "btn-dot-spinner d-none";
        spinner.setAttribute("aria-hidden", "true");
        spinner.innerHTML = "<span></span><span></span><span></span>";
        return spinner;
    }

    function ensureSpinnerMarkup(element) {
        if (!element.querySelector(".button-text")) {
            const textWrap = document.createElement("span");
            textWrap.className = "button-text";
            while (element.firstChild) {
                textWrap.appendChild(element.firstChild);
            }
            element.appendChild(textWrap);
        }

        if (!element.querySelector(".btn-dot-spinner")) {
            element.prepend(createDotsSpinner());
        }
    }

    function setLoadingState(element) {
        if (!element || element.classList.contains("loading")) return;
        ensureSpinnerMarkup(element);
        element.classList.add("loading");

        if (element instanceof HTMLButtonElement) {
            element.disabled = true;
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const authPage = document.querySelector(".auth-page");
        if (!authPage) return;

        const submitButtons = Array.from(
            authPage.querySelectorAll('button[type="submit"]')
        );
        submitButtons.forEach((button) => {
            button.dataset.noSpinner = "1";
            button.classList.add("auth-dot-btn");
            ensureSpinnerMarkup(button);
        });

        const spinLinks = Array.from(
            authPage.querySelectorAll(".auth-link, .auth-login-back-link")
        );
        spinLinks.forEach((link) => {
            link.classList.add("auth-dot-link");
            ensureSpinnerMarkup(link);
        });
    });

    document.addEventListener("submit", (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.closest(".auth-page")) return;

        const submitter =
            event.submitter ||
            form.querySelector('button[type="submit"], input[type="submit"]');
        if (!(submitter instanceof HTMLButtonElement)) return;

        setLoadingState(submitter);
    });

    document.addEventListener("click", (event) => {
        const link = event.target.closest(".auth-dot-link");
        if (!(link instanceof HTMLAnchorElement)) return;
        if (!link.closest(".auth-page")) return;

        if (link.classList.contains("loading")) {
            event.preventDefault();
            return;
        }

        const targetHref = link.getAttribute("href");
        if (!targetHref || targetHref === "#") return;

        event.preventDefault();
        setLoadingState(link);

        window.setTimeout(() => {
            window.location.href = targetHref;
        }, 140);
    });
})();
