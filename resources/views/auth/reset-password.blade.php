@extends('layouts.auth')

@section('title', 'Reset Password')

@section('content')
<section class="auth-page auth-login-page">
    <div class="auth-login-frame">
        <div class="auth-login-shell">
            <aside class="auth-login-brand-panel">
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

                <div class="auth-login-pill">Password Update</div>

                <div class="auth-login-copy">
                    <h1>Reset Password</h1>
                    <p>Create a new password for your account and continue securely.</p>
                </div>

                <div class="auth-login-guide">
                    <h3>Password tips</h3>
                    <ul>
                        <li>Use at least 8 characters.</li>
                        <li>Include letters and numbers.</li>
                        <li>Confirm the same password before saving.</li>
                    </ul>
                </div>

                <div class="auth-login-watermark" aria-hidden="true">
                    <i class="bi bi-shield-lock"></i>
                </div>
            </aside>

            <div class="auth-login-form-panel">
                <div class="auth-login-form-card">
                    <div class="auth-login-title-row">
                        <h2 class="auth-login-title">
                            <i class="bi bi-key"></i>
                            <span>Set New Password</span>
                        </h2>
                    </div>

                    <p class="auth-login-subtitle">Update your password below to regain account access.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger auth-alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.update') }}" class="auth-form auth-login-form">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="auth-input-group">
                            <label for="reset_email">Email Address</label>
                            <div class="auth-input-wrap is-readonly">
                                <i class="bi bi-envelope auth-input-icon"></i>
                                <input id="reset_email" type="email" value="{{ $email }}" readonly>
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="password">New Password</label>
                            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                                <i class="bi bi-lock auth-input-icon"></i>
                                <input id="password" type="password" name="password" placeholder="Create a strong password" required autocomplete="new-password">
                            </div>
                            @error('password')
                                <div class="auth-field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="auth-input-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <div class="auth-input-wrap @error('password_confirmation') is-invalid @enderror">
                                <i class="bi bi-shield-lock auth-input-icon"></i>
                                <input id="password_confirmation" type="password" name="password_confirmation" placeholder="Repeat the new password" required autocomplete="new-password">
                            </div>
                            @error('password_confirmation')
                                <div class="auth-field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-brand auth-login-button">
                            <i class="bi bi-check-circle"></i>
                            <span>Save New Password</span>
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
</section>
@endsection
