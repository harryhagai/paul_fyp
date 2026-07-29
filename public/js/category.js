document.addEventListener('DOMContentLoaded', function () {
    var searchForm = document.getElementById('category-search-form');
    var resultsContainer = document.getElementById('category-results');
    var searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;
    var sortSelect = document.getElementById('sort-select');

    if (!searchForm || !resultsContainer) return;

    var debounceId;
    var controller;

    function buildUrlFromForm() {
        var url = new URL(searchForm.action, window.location.origin);
        var formData = new FormData(searchForm);

        if (sortSelect && sortSelect.value) {
            var parts = sortSelect.value.split('-');
            if (parts.length === 2) {
                formData.set('sort_by', parts[0]);
                formData.set('sort_order', parts[1]);
            }
        }

        formData.forEach(function (value, key) {
            if (value !== null && String(value).trim() !== '') {
                url.searchParams.set(key, value);
            }
        });

        url.searchParams.delete('sort');
        return url;
    }

    function fetchAndReplace(url) {
        if (controller) controller.abort();
        controller = new AbortController();

        fetch(url.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newResults = doc.getElementById('category-results');
                var newForm = doc.getElementById('category-search-form');
                var newFilterActions = doc.getElementById('filter-actions');

                if (newResults) {
                    resultsContainer.innerHTML = newResults.innerHTML;
                }

                if (newFilterActions) {
                    var currentFilterActions = document.getElementById('filter-actions');
                    if (currentFilterActions) currentFilterActions.innerHTML = newFilterActions.innerHTML;
                }

                if (newForm) {
                    var hiddenNames = ['sort_by', 'sort_order', 'in_stock', 'on_sale', 'rating'];
                    hiddenNames.forEach(function (name) {
                        var currentInput = searchForm.querySelector('input[name="' + name + '"]');
                        var nextInput = newForm.querySelector('input[name="' + name + '"]');
                        if (currentInput && nextInput) currentInput.value = nextInput.value;
                    });
                }

                window.history.replaceState({}, '', url.toString());
            })
            .catch(function (error) {
                if (error.name === 'AbortError') return;
                console.error(error);
            });
    }

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        fetchAndReplace(buildUrlFromForm());
    });

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceId);
            debounceId = setTimeout(function () {
                fetchAndReplace(buildUrlFromForm());
            }, 250);
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            fetchAndReplace(buildUrlFromForm());
        });
    }

    document.addEventListener('click', function (event) {
        var filterLink = event.target.closest('[data-filter-link]');
        if (filterLink) {
            event.preventDefault();
            fetchAndReplace(new URL(filterLink.href, window.location.origin));
            return;
        }

        var pageLink = event.target.closest('#category-results .pagination a');
        if (pageLink) {
            event.preventDefault();
            fetchAndReplace(new URL(pageLink.href, window.location.origin));
        }
    });
});
