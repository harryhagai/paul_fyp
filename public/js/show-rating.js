// Product rating functions.

window.openRatingModal = async function () {
    if (!isUserAuthenticated()) {
        const result = await fireStyled({
            title: "Account Required",
            html: `
                Sorry Dear Customer! <br><br>
                <strong>You need an account to rate products and share your review.</strong><br><br>
                If you already have an account, please login.<br>
                If you don't have an account yet, please register first.
            `,
            icon: "info",
            showCancelButton: true,
            showCloseButton: true,
            confirmButtonText: '<i class="bi bi-person-circle me-1"></i>Login',
            cancelButtonText: '<i class="bi bi-person-plus me-1"></i>Register',
            reverseButtons: true,
        });

        if (result.isConfirmed) {
            window.location.href = buildAuthRedirectUrl("/login", {
                redirect: window.location.pathname + window.location.search,
                action: "rate_product",
                product_id: window.productId,
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            window.location.href = buildAuthRedirectUrl("/register", {
                redirect: window.location.pathname + window.location.search,
                action: "rate_product",
                product_id: window.productId,
            });
        }
        return;
    }

    if (!isCurrentUserEmailVerified()) {
        window.location.href = buildAuthRedirectUrl("/email/verify", {
            redirect: window.location.pathname + window.location.search,
            action: "rate_product",
            product_id: window.productId,
        });
        return;
    }

    const ratingModalEl = document.getElementById("ratingModal");
    if (!ratingModalEl || typeof bootstrap === "undefined") return;

    const modal = bootstrap.Modal.getOrCreateInstance(ratingModalEl);
    modal.show();
};

function initStarRating() {
    const stars = document.querySelectorAll(".star-rating");
    stars.forEach((star, index) => {
        star.addEventListener("click", function () {
            const rating = this.previousElementSibling?.value;
            if (!rating) return;

            document
                .querySelectorAll(".star-rating")
                .forEach((s) => s.classList.remove("active", "hover"));

            const allStars = document.querySelectorAll(".star-rating");
            for (let i = 0; i <= index; i++) {
                allStars[i]?.classList.add("active");
            }

            const radio = document.getElementById("star" + rating);
            if (radio) radio.checked = true;
        });

        star.addEventListener("mouseenter", function () {
            const allStars = document.querySelectorAll(".star-rating");
            for (let i = 0; i <= index; i++) {
                allStars[i]?.classList.add("hover");
            }
        });

        star.addEventListener("mouseleave", function () {
            document
                .querySelectorAll(".star-rating")
                .forEach((s) => s.classList.remove("hover"));
        });
    });
}

function initRatingForm() {
    const ratingForm = document.querySelector("#ratingModal form");
    const submitBtn = document.getElementById("submitRatingBtn");

    if (!ratingForm || !submitBtn) return;

    ratingForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        try {
            const formData = new FormData(ratingForm);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML =
                '<i class="bi bi-arrow-repeat spinner"></i> Submitting...';

            const response = await fetch(ratingForm.action, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": data._token,
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                const modal = bootstrap.Modal.getInstance(
                    document.getElementById("ratingModal")
                );
                if (modal) modal.hide();

                await fireStyled({
                    title: "Thank You!",
                    text: "Your rating has been submitted successfully.",
                    icon: "success",
                    color: "#1f2937",
                    background: "#ffffff",
                    confirmButtonColor: THEME_PRIMARY,
                    confirmButtonText: '<i class="bi bi-check-circle me-1"></i>Great',
                    showCloseButton: true,
                    timer: 3500,
                    timerProgressBar: true,
                    customClass: {
                        popup: "swal-wide",
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });

                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                await fireStyled({
                    title: "Error",
                    text: result.message || "Something went wrong",
                    icon: "error",
                    confirmButtonColor: THEME_PRIMARY,
                });
            }
        } catch (error) {
            console.error("Error submitting rating:", error);
            await fireStyled({
                title: "Error",
                text: "An error occurred. Please try again.",
                icon: "error",
                confirmButtonColor: THEME_PRIMARY,
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="bi bi-send"></i> Submit Rating';
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initStarRating();
    initRatingForm();

    const shouldOpenRatingModal =
        isUserAuthenticated() &&
        new URLSearchParams(window.location.search).get("open_rating") === "1";
    if (shouldOpenRatingModal) {
        openRatingModal();

        const url = new URL(window.location.href);
        url.searchParams.delete("open_rating");
        window.history.replaceState({}, "", url.pathname + url.search + url.hash);
    }
});
