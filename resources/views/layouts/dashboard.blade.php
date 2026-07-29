<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Kids Shop')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://code.jquery.com" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//code.jquery.com">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" crossorigin="anonymous" referrerpolicy="no-referrer">

    <link href="{{ asset('css/sellerHeader.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sellerSidebar.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sellerLayout.css') }}" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
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
    </style>

    @yield('styles')
</head>

<body class="seller-body">
    @include('components.dashboardHeader')
    @include('components.dashboardSidebar')

    <div id="seller-main">
        <main id="seller-content" class="pb-5">
            @yield('content')
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/sellerSidebarToggler.js') }}"></script>
    <script src="{{ asset('js/buttonSpinner.js') }}"></script>
    <script src="{{ asset('js/sidebarMenuSpinner.js') }}"></script>

    @yield('scripts')

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                showConfirmButton: true
            });
        </script>
    @endif

    @stack('scripts')
</body>

</html>
