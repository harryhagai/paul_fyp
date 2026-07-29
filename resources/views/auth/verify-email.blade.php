@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<section class="auth-page py-2 py-md-3">
    <div class="container-fluid px-0">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-11 col-md-10 col-lg-7 col-xl-6">
                <div class="card auth-bs-card border-0 shadow-sm">
                    <div class="auth-login-form-panel auth-bs-panel">
                        <div class="auth-login-form-card w-100 text-center">
                            <div class="auth-mobile-logo d-flex">
                                <img src="{{ asset('img/logo.png') }}" alt="Logo">
                            </div>

                            <h2 class="auth-login-title mb-2 justify-content-center">
                                <i class="bi bi-envelope-check"></i>
                                <span>Verify Your Email</span>
                            </h2>
                            <p class="auth-login-subtitle mb-3">
                                A verification link has been sent to
                                <strong class="d-block mt-1">{{ auth()->user()->email }}</strong>
                                Please click the link to activate your account.
                            </p>

                            @if (session('status'))
                                <div class="alert alert-success auth-alert">{{ session('status') }}</div>
                            @endif

                            <form method="POST" action="{{ route('verification.send') }}">
                                @csrf
                                <button type="submit" class="btn btn-brand">
                                    <i class="bi bi-send"></i>
                                    <span>Resend Verification Link</span>
                                </button>
                            </form>

                            <div class="auth-login-back-wrap">
                                <a href="{{ route('shop') }}" class="auth-login-back-link">
                                    <i class="bi bi-arrow-left"></i>
                                    <span>Back to Shop</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
