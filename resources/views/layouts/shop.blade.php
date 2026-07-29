<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'TotoNest'))</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <style>
        @php
            $systemColors = \App\Models\SiteSetting::whereIn('key', ['theme_primary_color', 'theme_secondary_color', 'theme_bg_color'])->pluck('value', 'key');
        @endphp

        :root {
            @if (isset($systemColors['theme_primary_color']))
                --teal-primary: {{ $systemColors['theme_primary_color'] }};
            @endif
            @if (isset($systemColors['theme_secondary_color']))
                --teal-secondary: {{ $systemColors['theme_secondary_color'] }};
                --teal-light: {{ $systemColors['theme_secondary_color'] }};
            @endif
            @if (isset($systemColors['theme_bg_color']))
                --teal-bg: {{ $systemColors['theme_bg_color'] }};
            @endif
        }

        body {
            margin: 0;
            padding: 0;
        }

        .category-delete-popup {
            border-radius: 20px;
            width: 420px;
            max-width: calc(100vw - 2rem);
            padding: 0.85rem 0.75rem 1rem;
        }

        .category-delete-icon.swal2-icon {
            border-width: 0.2em;
        }

        .category-delete-icon.swal2-icon.swal2-info,
        .category-delete-icon.swal2-icon.swal2-success,
        .category-delete-icon.swal2-icon.swal2-error,
        .category-delete-icon.swal2-icon.swal2-question {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
        }

        .category-delete-icon.swal2-icon.swal2-info .swal2-icon-content,
        .category-delete-icon.swal2-icon.swal2-success .swal2-icon-content,
        .category-delete-icon.swal2-icon.swal2-error .swal2-icon-content,
        .category-delete-icon.swal2-icon.swal2-question .swal2-icon-content {
            color: var(--teal-primary, #0d9488);
        }

        .category-delete-icon.swal2-icon.swal2-warning {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
        }

        .category-delete-icon.swal2-icon.swal2-warning .swal2-icon-content {
            color: var(--teal-primary, #0d9488);
        }

        .category-delete-title {
            font-size: 1.6rem;
            font-weight: 400;
            color: #4b5563;
        }

        .category-delete-text {
            font-size: 0.96rem;
            color: #6b7280;
            line-height: 1.6;
        }

        .category-delete-actions {
            gap: 0.75rem;
        }

        .category-delete-confirm,
        .category-delete-cancel {
            background: transparent;
            border: 1px solid;
            border-radius: 10px;
            padding: 0.65rem 1.35rem;
            font-weight: 400;
            line-height: 1;
        }

        .category-delete-confirm {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
        }

        .category-delete-confirm:hover {
            background: var(--teal-primary, #0d9488);
            color: #fff;
        }

        .category-delete-confirm:focus,
        .category-delete-confirm:focus-visible {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
            box-shadow: none;
            outline: none;
        }

        .category-delete-cancel {
            border-color: #9ca3af;
            color: #6b7280;
        }

        .category-delete-cancel:hover {
            background: #6b7280;
            color: #fff;
        }

        .category-delete-popup .swal2-close {
            color: #9ca3af;
            transition: color 0.2s ease;
        }

        .category-delete-popup .swal2-close:hover {
            color: var(--teal-primary, #0d9488);
        }
    </style>

    @yield('css')
</head>

<body>
    <main>
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('js/buttonSpinner.js') }}"></script>

    @if (session('success'))
        @php
            $successMessage = (string) session('success');
        @endphp
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: @json($successMessage),
                color: '#4b5563',
                background: '#ffffff',
                confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--teal-primary').trim() || '#0d9488'
            });
        </script>
    @endif

    @if (session('error'))
        @php
            $errorMessage = (string) session('error');
            $normalizedError = \Illuminate\Support\Str::lower($errorMessage);
            $isCartEmptyError = \Illuminate\Support\Str::contains($normalizedError, 'cart is empty');
        @endphp
        <script>
            Swal.fire({
                icon: 'error',
                title: @json($isCartEmptyError ? 'Your Cart Is Empty' : 'Something Went Wrong'),
                text: @json($isCartEmptyError ? 'Your cart is empty. Start shopping to add items you love.' : $errorMessage),
                color: '#4b5563',
                background: '#ffffff',
                confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--teal-primary').trim() || '#0d9488',
                confirmButtonText: '<i class="bi bi-arrow-left-right me-1"></i>Continue Shopping',
                customClass: {
                    popup: 'category-delete-popup',
                    icon: 'category-delete-icon',
                    title: 'category-delete-title',
                    htmlContainer: 'category-delete-text',
                    actions: 'category-delete-actions',
                    confirmButton: 'category-delete-confirm btn',
                },
                buttonsStyling: false,
                didOpen: (popup) => {
                    const confirmBtn = popup.querySelector('.swal2-confirm');
                    const themeColor = getComputedStyle(document.documentElement).getPropertyValue('--teal-primary').trim() || '#0d9488';
                    if (confirmBtn) {
                        confirmBtn.style.backgroundColor = themeColor;
                        confirmBtn.style.borderColor = themeColor;
                        confirmBtn.style.color = '#fff';
                    }
                    const errorLines = popup.querySelectorAll('.swal2-x-mark-line-left, .swal2-x-mark-line-right');
                    errorLines.forEach((line) => {
                        line.style.backgroundColor = themeColor;
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/shop';
                }
            });
        </script>
    @endif

    @yield('scripts')
</body>

</html>
