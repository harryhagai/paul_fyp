@extends('layouts.auth')

@section('title', 'Forgot Password')

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

                                <div class="auth-login-pill">Password Recovery</div>

                                <div class="auth-login-copy">
                                    <h1>Forgot Password</h1>
                                    <p>Enter your registered email address and we will send a secure password reset link.</p>
                                </div>

                                <div class="auth-login-guide">
                                    <h3>How this works</h3>
                                    <ul>
                                        <li>Enter the email address linked to your account.</li>
                                        <li>The system verifies whether the account exists.</li>
                                        <li>If valid, a reset link is sent to the same email.</li>
                                    </ul>
                                </div>

                                <div class="auth-login-watermark" aria-hidden="true">
                                    <i class="bi bi-key"></i>
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
                                        <i class="bi bi-envelope-paper"></i>
                                        <span>Request Reset Link</span>
                                    </h2>

                                    <p class="auth-login-subtitle">Use your account email to receive a password reset link.</p>

                                    @if (session('status'))
                                        <div class="alert alert-success auth-alert">{{ session('status') }}</div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger auth-alert">{{ $errors->first() }}</div>
                                    @endif

                                    <form method="POST" action="{{ route('password.email') }}" class="auth-form auth-login-form">
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

                                        <button type="submit" class="btn btn-brand auth-login-button">
                                            <i class="bi bi-send"></i>
                                            <span>Send Reset Link</span>
                                        </button>

                                        <div class="auth-login-back-wrap">
                                            <a href="{{ route('login') }}" class="auth-login-back-link">
                                                <i class="bi bi-arrow-left"></i>
                                                <span>Back to Login</span>
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
