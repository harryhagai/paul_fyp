document.addEventListener('DOMContentLoaded', function () {
    var searchForm = document.getElementById('categories-search-form');
    var resultsContainer = document.getElementById('categories-results');

    if (!searchForm || !resultsContainer) return;

    var searchInput = searchForm.querySelector('input[name="search"]');
    if (!searchInput) return;

    var debounceId;
    var controller;

    function updateResults(searchValue) {
        if (controller) {
            controller.abort();
        }

        controller = new AbortController();
        var url = new URL(searchForm.action, window.location.origin);

        if (searchValue) {
            url.searchParams.set('search', searchValue);
        }

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Search request failed');
                }
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var newResults = doc.getElementById('categories-results');

                if (!newResults) return;

                resultsContainer.innerHTML = newResults.innerHTML;

                var cleanUrl = new URL(window.location.href);
                if (searchValue) {
                    cleanUrl.searchParams.set('search', searchValue);
                } else {
                    cleanUrl.searchParams.delete('search');
                }
                window.history.replaceState({}, '', cleanUrl.toString());
            })
            .catch(function (error) {
                if (error.name === 'AbortError') return;
                console.error(error);
            });
    }

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        updateResults(searchInput.value.trim());
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceId);
        debounceId = setTimeout(function () {
            updateResults(searchInput.value.trim());
        }, 250);
    });
});
