@extends('layouts.auth')

@section('title', 'Register')

@section('content')
@php
    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
@endphp
<section class="auth-page py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-11 col-xl-10">
                <div class="card auth-bs-card border-0 shadow-sm overflow-hidden">
                    <div class="row g-0">
                        <div class="col-12 col-lg-6 d-flex">
                            <aside class="auth-login-brand-panel h-100">
                                <div class="auth-login-brand">
                                    <img
                                        src="{{ isset($headerSettings['header_logo']) ? asset($headerSettings['header_logo']) : asset('img/logo.png') }}"
                                        alt="{{ $headerSettings['header_school_name'] ?? 'KidsStore' }} Logo">
                                    <span>
                                        <strong>{{ $headerSettings['header_school_name'] ?? 'KidsStore' }}</strong>
                                        <small>{{ $headerSettings['header_school_subtitle'] ?? 'Premium Kids Shopping Experience' }}</small>
                                    </span>
                                </div>

                                <div class="auth-login-pill">Create Account</div>

                                <div class="auth-login-copy">
                                    <h1>Join Us Today</h1>
                                    <p>Create your account to enjoy a faster, secure, and personalized shopping experience.</p>
                                </div>

                                <div class="auth-login-guide">
                                    <h3>Before you register</h3>
                                    <ul>
                                        <li>Use your real name and active email address.</li>
                                        <li>Add a valid phone number you can access.</li>
                                        <li>Choose a strong password to protect your account.</li>
                                    </ul>
                                </div>

                                <div class="auth-login-watermark" aria-hidden="true">
                                    <i class="bi bi-person-check"></i>
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
                                        <i class="bi bi-person-plus"></i>
                                        <span>Register</span>
                                    </h2>

                                    <p class="auth-login-subtitle">Fill in your details to create an account.</p>

                                    @if ($errors->any())
                                        <div class="alert alert-danger auth-alert">{{ $errors->first() }}</div>
                                    @endif

                                    <form method="POST" action="{{ route('register') }}" class="auth-form auth-login-form">
                                        @csrf

                                        <div class="auth-input-group">
                                            <label for="name">Full Name</label>
                                            <div class="auth-input-wrap @error('name') is-invalid @enderror">
                                                <i class="bi bi-person auth-input-icon"></i>
                                                <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder="Your full name" required autocomplete="name" autofocus>
                                            </div>
                                            @error('name')<div class="auth-field-error">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="auth-input-group">
                                            <label for="email">Email Address</label>
                                            <div class="auth-input-wrap @error('email') is-invalid @enderror">
                                                <i class="bi bi-envelope auth-input-icon"></i>
                                                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autocomplete="email">
                                            </div>
                                            @error('email')<div class="auth-field-error">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="auth-input-group">
                                            <label for="phone_number">Phone Number</label>
                                            <div class="auth-input-wrap @error('phone_number') is-invalid @enderror">
                                                <i class="bi bi-telephone auth-input-icon"></i>
                                                <input id="phone_number" type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="e.g. 255712345678" required autocomplete="tel">
                                            </div>
                                            @error('phone_number')<div class="auth-field-error">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="auth-input-group">
                                            <label for="password">Password</label>
                                            <div class="auth-input-wrap @error('password') is-invalid @enderror">
                                                <i class="bi bi-lock auth-input-icon"></i>
                                                <input id="password" type="password" name="password" placeholder="Create password" required autocomplete="new-password">
                                            </div>
                                            @error('password')<div class="auth-field-error">{{ $message }}</div>@enderror
                                        </div>

                                        <button type="submit" class="btn btn-brand auth-login-button">
                                            <i class="bi bi-person-plus"></i>
                                            <span>Create Account</span>
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
