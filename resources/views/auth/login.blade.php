@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<section class="auth-page py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-11 col-xl-10">
                <div class="card auth-bs-card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-12 col-lg-6 d-flex">
                            <aside class="auth-login-brand-panel h-100">
                                @php
                                    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
                                @endphp
                                <div class="auth-login-brand">
                                    <img
                                        src="{{ isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png') }}"
                                        alt="{{ $headerSettings['header_school_name'] ?? 'KidsStore' }} Logo">
                                    <span>
                                        <strong>{{ $headerSettings['header_school_name'] ?? 'KidsStore' }}</strong>
                                        <small>{{ $headerSettings['header_school_subtitle'] ?? 'Premium Kids Shopping Experience' }}</small>
                                    </span>
                                </div>

                                <div class="auth-login-pill">Secure Access</div>

                                <div class="auth-login-copy">
                                    <h1>Welcome Back</h1>
                                    <p>Sign in to continue with secure access to your KidsStore services.</p>
                                </div>

                                <div class="auth-login-guide">
                                    <h3>How to fill this form</h3>
                                    <ul>
                                        <li>Use your registered email address and password.</li>
                                        <li>Keep your account active with protected sessions.</li>
                                        <li>Reset your password quickly if you lose access.</li>
                                    </ul>
                                </div>

                                <div class="auth-login-watermark" aria-hidden="true">
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                            </aside>
                        </div>

                        <div class="col-12 col-lg-6">
                            <div class="auth-login-form-panel auth-bs-panel">
                                <div class="auth-login-form-card w-100">
                                    <div class="auth-mobile-logo d-lg-none">
                                        <img
                                            src="{{ isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png') }}"
                                            alt="{{ $headerSettings['header_school_name'] ?? 'KidsStore' }} Logo">
                                    </div>
                                    <h2 class="auth-login-title mb-2">
                                        <i class="bi bi-box-arrow-in-right"></i>
                                        <span>Login</span>
                                    </h2>

                                    <p class="auth-login-subtitle">Enter your credentials to access your account.</p>

                                    @if ($errors->any())
                                        <div class="alert alert-danger auth-alert">{{ $errors->first() }}</div>
                                    @endif

                                    <form method="POST" action="{{ route('login') }}" class="auth-form auth-login-form">
                                        @csrf

                                        <div class="auth-input-group">
                                            <label for="email">Email Address</label>
                                            <div class="auth-input-wrap @error('email') is-invalid @enderror">
                                                <i class="bi bi-envelope auth-input-icon"></i>
                                                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autocomplete="email" autofocus>
                                            </div>
                                            @error('email')
                                                <div class="auth-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="auth-input-group">
                                            <label for="password">Password</label>
                                            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                                                <i class="bi bi-lock auth-input-icon"></i>
                                                <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                            </div>
                                            @error('password')
                                                <div class="auth-field-error">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="auth-form-meta auth-login-meta flex-wrap gap-2">
                                            <label class="auth-checkbox" for="remember">
                                                <input id="remember" type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                                <span>Remember me</span>
                                            </label>
                                            <a href="{{ route('password.request') }}" class="auth-link auth-dot-link" data-spin-link="1">
                                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text">Forgot Password?</span>
                                            </a>
                                        </div>

                                        <button type="submit" class="btn btn-brand auth-login-button" data-no-spinner>
                                            <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                            <span class="button-text">
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>Login</span>
                                            </span>
                                        </button>

                                        <div class="auth-login-back-wrap">
                                            <a href="{{ route('register') }}" class="auth-login-back-link auth-dot-link" data-spin-link="1">
                                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text"><span class="auth-inline-hint">Don't have an account? </span><span class="auth-inline-cta">Register</span></span>
                                            </a>
                                        </div>

                                        <div class="auth-login-back-wrap">
                                            <a href="{{ route('shop') }}" class="auth-login-back-link auth-dot-link" data-spin-link="1">
                                                <span class="btn-dot-spinner d-none" aria-hidden="true"><span></span><span></span><span></span></span>
                                                <span class="button-text">
                                                    <i class="bi bi-arrow-left"></i>
                                                    <span>Back to Shop</span>
                                                </span>
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
