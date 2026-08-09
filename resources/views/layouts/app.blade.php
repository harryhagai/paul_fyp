<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'TotoNest') }}</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- SweetAlert2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">

    {{-- bootsrap 5 icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    {{-- Custom CSS --}}
    <link href="{{ asset('css/header.css') }}" rel="stylesheet">
    @unless (trim($__env->yieldContent('hideFooter')) === 'true')
        <link href="{{ asset('css/footer.css') }}" rel="stylesheet">
    @endunless

    <style>
        @php
            $systemColors = \App\Models\SiteSetting::whereIn('key', ['theme_primary_color', 'theme_secondary_color', 'theme_bg_color'])->pluck('value', 'key');
        @endphp
        :root {
            @if(isset($systemColors['theme_primary_color']))
            --teal-primary: {{ $systemColors['theme_primary_color'] }};
            @endif
            @if(isset($systemColors['theme_secondary_color']))
            --teal-secondary: {{ $systemColors['theme_secondary_color'] }};
            --teal-light: {{ $systemColors['theme_secondary_color'] }};
            @endif
            @if(isset($systemColors['theme_bg_color']))
            --teal-bg: {{ $systemColors['theme_bg_color'] }};
            @endif
        }
        
        body {
            margin: 0;
            padding: 0;
        }

        /* hero spacing handled by home.css */

        .category-delete-popup {
            border-radius: 24px;
            padding: 1.75rem 1.5rem 1.25rem;
            overflow: hidden;
            position: relative;
        }

        .category-delete-popup .swal2-timer-progress-bar-container {
            left: 0;
            right: 0;
            bottom: 0;
            border-bottom-left-radius: 24px;
            border-bottom-right-radius: 24px;
        }

        .category-delete-title {
            color: #4b5563;
            font-weight: 500;
        }

        .category-delete-text {
            color: #6b7280;
        }

        .category-delete-confirm,
        .category-delete-cancel {
            background: transparent;
            border: 1px solid;
            border-radius: 12px;
            padding: 0.7rem 1.6rem;
            font-weight: 500;
            line-height: 1;
        }

        .category-delete-confirm {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
            background: #fff;
        }

        .category-delete-confirm:hover {
            background: var(--teal-primary, #0d9488);
            color: #fff;
        }

        .category-delete-confirm:focus,
        .category-delete-confirm:focus-visible {
            border-color: var(--teal-primary, #0d9488);
            color: var(--teal-primary, #0d9488);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--teal-primary, #0d9488) 22%, transparent);
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
    </style>
    {{-- Extra CSS from pages --}}
    @yield('css')
</head>

<body>

    {{-- Header Component --}}
    @include('components.header')

    {{-- Main Content --}}
  <main>
    @yield('content')
</main>


    @unless (trim($__env->yieldContent('hideFooter')) === 'true')
        {{-- Footer Component --}}
        @include('components.footer')
    @endunless

    {{-- Bootstrap JS Bundle --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    {{-- Custom JS --}}
    <script src="{{ asset('js/header.js') }}"></script>
    <script src="{{ asset('js/buttonSpinner.js') }}"></script>
    @unless (trim($__env->yieldContent('hideFooter')) === 'true')
        <script src="{{ asset('js/footer.js') }}"></script>
    @endunless

    @auth
    {{-- Auto Logout on Inactivity 
    <script>
        let inactivityTime = function () {
            let time;
            let countdownInterval;
            let remainingTime = 300; // 5 minutes in seconds

            console.log('Auto-logout script initialized. Timer will start on first activity.');
            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;
            document.onmousedown = resetTimer;
            document.ontouchstart = resetTimer;
            document.onclick = resetTimer;
            document.onkeydown = resetTimer;
            document.addEventListener('scroll', resetTimer, true);

            function logout() {
                clearInterval(countdownInterval);
                console.log('Logging out due to inactivity...');
                // Submit the logout form
                document.getElementById('logout-form').submit();
            }

            function resetTimer() {
                clearTimeout(time);
                clearInterval(countdownInterval);
                remainingTime = 300;
                time = setTimeout(logout, 300000); // 5 minutes = 300000 ms
                console.log('Activity detected. Logout timer reset to 5 minutes.');

                countdownInterval = setInterval(() => {
                    remainingTime--;
                    if (remainingTime <= 0) {
                        clearInterval(countdownInterval);
                    } else if (remainingTime % 60 === 0 || remainingTime <= 10) { // Log every minute or last 10 seconds
                        console.log(`Time remaining to auto-logout: ${Math.floor(remainingTime / 60)}:${(remainingTime % 60).toString().padStart(2, '0')} minutes`);
                    }
                }, 1000);
            }
        };
        inactivityTime();
    </script>
--}}
    {{-- Hidden Logout Form --}}
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    @endauth

    @if (session('success'))
    @php
        $successMessage = (string) session('success');
        $normalizedSuccess = \Illuminate\Support\Str::lower($successMessage);
        $isCartSuccess = \Illuminate\Support\Str::contains($normalizedSuccess, 'cart');
    @endphp
    <script>
        const successMessage = @json($successMessage);
        const isCartSuccess = @json($isCartSuccess);

        const baseOptions = {
            text: successMessage,
            icon: 'success',
            color: '#4b5563',
            background: '#ffffff',
            confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--teal-primary').trim() || '#0d9488',
            showCloseButton: true,
            timer: 3500,
            timerProgressBar: true,
            customClass: {
                popup: 'category-delete-popup',
                title: 'category-delete-title',
                htmlContainer: 'category-delete-text',
                actions: 'category-delete-actions',
                confirmButton: 'category-delete-confirm',
                cancelButton: 'category-delete-cancel',
            },
            buttonsStyling: false
        };

        if (isCartSuccess) {
            Swal.fire({
                ...baseOptions,
                title: 'Item Added to Cart!',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-cart3 me-1"></i>View Cart',
                showCancelButton: true,
                cancelButtonText: '<i class="bi bi-arrow-left-right me-1"></i>Continue Shopping',
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/cart';
                }
            });
        } else {
            Swal.fire({
                ...baseOptions,
                title: 'Success',
                confirmButtonText: 'OK'
            });
        }
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: @json(session('error')),
            showConfirmButton: true
        });
    </script>
    @endif

    {{-- Extra JS from pages --}}
    @yield('scripts')

</body>

</html>
