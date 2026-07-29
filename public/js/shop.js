document.addEventListener('DOMContentLoaded', function () {
    var searchForm = document.getElementById('shop-search-form');
    var searchInput = searchForm ? searchForm.querySelector('input[name="search"]') : null;
    var categoriesGrid = document.getElementById('shop-categories-grid');
    var productsContainer = document.getElementById('productsContainer');
    var paginationContainer = document.getElementById('shop-pagination');
    var sortSelect = document.getElementById('shop-sort-select');
    var searchSpinner = document.getElementById('search-loading-spinner');
    var infiniteLoader = document.getElementById('shop-infinite-loader');
    var infiniteEnd = document.getElementById('shop-infinite-end');
    var scrollSentinel = document.getElementById('shop-scroll-sentinel');

    if (!searchForm || !searchInput || !categoriesGrid || !productsContainer || !paginationContainer) return;

    var debounceId;
    var controller;
    var currentParams = new URLSearchParams(window.location.search);
    var nextPageUrl = null;
    var isLoadingMore = false;
    var observer = null;

    function requestUrlFromParams() {
        var url = new URL(searchForm.action, window.location.origin);
        url.search = currentParams.toString();
        return url;
    }

    function getNextPageUrlFromDoc(doc) {
        var nextLink = doc.querySelector('#shop-pagination .pagination .page-item.active + .page-item a.page-link');
        if (!nextLink) {
            nextLink = doc.querySelector('#shop-pagination .pagination a[rel="next"]');
        }
        return nextLink ? nextLink.href : null;
    }

    function setInfiniteState() {
        if (infiniteEnd) infiniteEnd.style.display = nextPageUrl ? 'none' : '';
    }

    function fetchAndReplace(url) {
        if (controller) controller.abort();
        controller = new AbortController();

        // Show spinner and hide products while loading.
        if (searchSpinner) searchSpinner.style.display = '';
        productsContainer.style.display = 'none';

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

                var nextCategories = doc.getElementById('shop-categories-grid');
                var nextProducts = doc.getElementById('productsContainer');
                var nextPagination = doc.getElementById('shop-pagination');
                var nextSearch = doc.querySelector('#shop-search-form input[name="search"]');
                var nextSortSelect = doc.getElementById('shop-sort-select');

                if (nextCategories) categoriesGrid.innerHTML = nextCategories.innerHTML;
                if (nextProducts) productsContainer.innerHTML = nextProducts.innerHTML;
                if (nextPagination) paginationContainer.innerHTML = nextPagination.innerHTML;

                if (nextSearch) searchInput.value = nextSearch.value;
                if (sortSelect && nextSortSelect) sortSelect.value = nextSortSelect.value;
                nextPageUrl = getNextPageUrlFromDoc(doc);
                setInfiniteState();

                // Keep URL clean while using AJAX filters/search.
                window.history.replaceState({}, '', window.location.pathname);
            })
            .catch(function (error) {
                if (error.name === 'AbortError') return;
                console.error(error);
            })
            .finally(function () {
                // Hide spinner, show products
                if (searchSpinner) searchSpinner.style.display = 'none';
                productsContainer.style.display = '';
            });
    }

    function appendNextProducts() {
        if (!nextPageUrl || isLoadingMore) return;

        isLoadingMore = true;
        if (infiniteLoader) infiniteLoader.style.display = '';

        fetch(nextPageUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.text();
            })
            .then(function (html) {
                var parser = new DOMParser();
                var doc = parser.parseFromString(html, 'text/html');
                var nextProducts = doc.getElementById('productsContainer');
                var nextPagination = doc.getElementById('shop-pagination');

                if (nextProducts) {
                    var articles = nextProducts.querySelectorAll('.product-card');
                    articles.forEach(function (article) {
                        productsContainer.appendChild(article);
                    });
                }

                if (nextPagination) {
                    paginationContainer.innerHTML = nextPagination.innerHTML;
                }

                nextPageUrl = getNextPageUrlFromDoc(doc);
                setInfiniteState();
            })
            .catch(function (error) {
                console.error(error);
            })
            .finally(function () {
                isLoadingMore = false;
                if (infiniteLoader) infiniteLoader.style.display = 'none';
            });
    }

    function resetInfiniteObserver() {
        if (!scrollSentinel) return;
        if (observer) observer.disconnect();

        observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    appendNextProducts();
                }
            });
        }, { rootMargin: '600px 0px' });

        observer.observe(scrollSentinel);
    }

    function buildSearchUrl() {
        var searchValue = searchInput.value.trim();

        if (searchValue) {
            currentParams.set('search', searchValue);
        } else {
            currentParams.delete('search');
        }

        currentParams.delete('page');
        return requestUrlFromParams();
    }

    searchForm.addEventListener('submit', function (event) {
        event.preventDefault();
        fetchAndReplace(buildSearchUrl());
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceId);
        debounceId = setTimeout(function () {
            fetchAndReplace(buildSearchUrl());
        }, 250);
    });

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            var sortValue = this.value;

            if (!sortValue) {
                currentParams.delete('sort_by');
                currentParams.delete('sort_order');
            } else {
                var parts = sortValue.split('-');
                if (parts.length === 2) {
                    currentParams.set('sort_by', parts[0]);
                    currentParams.set('sort_order', parts[1]);
                }
            }

            currentParams.delete('page');
            fetchAndReplace(requestUrlFromParams());
        });
    }

    document.addEventListener('click', function (event) {
        var categoryLink = event.target.closest('#shop-categories-grid a.category-pill');
        if (categoryLink) {
            event.preventDefault();
            // Use the link's URL directly — it already has all params (search, sort, etc.)
            currentParams = new URLSearchParams(new URL(categoryLink.href, window.location.origin).search);
            fetchAndReplace(requestUrlFromParams());
            return;
        }

        var pageLink = event.target.closest('#shop-pagination .pagination a');
        if (pageLink) {
            event.preventDefault();
            var pagingUrl = new URL(pageLink.href, window.location.origin);
            currentParams = new URLSearchParams(pagingUrl.search);
            fetchAndReplace(requestUrlFromParams());
        }
    });

    searchInput.addEventListener('focus', function () {
        var wrap = this.closest('.search-bar');
        if (wrap) wrap.classList.add('search-focused');
    });

    searchInput.addEventListener('blur', function () {
        var wrap = this.closest('.search-bar');
        if (wrap) wrap.classList.remove('search-focused');
    });

    nextPageUrl = getNextPageUrlFromDoc(document);
    setInfiniteState();
    resetInfiniteObserver();
});
