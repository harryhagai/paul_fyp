<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Account') - KidsStore</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="{{ asset('css/auth.css') }}" rel="stylesheet">
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
<body>
    <main class="auth-shell">
        <div class="auth-bg-shape auth-bg-shape-1"></div>
        <div class="auth-bg-shape auth-bg-shape-2"></div>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/buttonSpinner.js') }}"></script>
    <script src="{{ asset('js/authSpinner.js') }}"></script>
    @yield('scripts')
</body>
</html>
